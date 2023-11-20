console.log("This is subscribers file ");
function initSubscription() {
  console.log(`Subscription initialized Successfully`);
}

window.addEventListener("load", async () => {
  setTimeout(()=>{
    // setup variables after page load
    initExtension();
  },2000);
  
  setInterval(()=>{ 
    // Keep checking order book
    // checkNewTrails();
  }, 5000);

  console.log('Loaded Breakout js');
});

let whatchlistSymbols = [];
let symbolOpenPrice   = {};
let symbolIDMap       = {
                          'NELCAST' : 3778817,
                          'ASHOKLEY': 54273
                        };

function initExtension() {
  console.log('init Breakout extension');
  // getWatchlistSymbols();
  // fetchWatchlistMinuteData();
  setTimeout(()=>{
    updateBollingerBands();
    // runEveryMinuteOnZeroSeconds();
  }, 1000);
}

function getWatchlistSymbols(){
  let instruments = getAllInstruments();
  // console.log(`the instruments`, instruments);
  for (let i=0 ;i<instruments.length; i++){
    let wrapper = instruments[i];
    let symbol = wrapper.querySelector('.nice-name').textContent;
    whatchlistSymbols.push(symbol);
  }
  console.log(`The watchlist symbols `,whatchlistSymbols);
}

async function fetchWatchlistMinuteData() {
  // console.log(`The watchlist symbols count `,whatchlistSymbols.length);
  let count = 1;
  let waitTime = 1000;
  while(count<=whatchlistSymbols.length) {
    let symbol = whatchlistSymbols[(count-1)];
    console.log(`While loop for `,symbol);
    let symbolID = symbolIDMap[symbol];
    if(symbolID) { 
      let symbolObj = {id: symbolID, symbol: symbol};
      await getApiData(symbolObj);
      waitTime = 500;
    }
    else {
      waitTime = 50;
    }

    count++;
    await new Promise(resolve => setTimeout(resolve, waitTime));
  }
}

function calculateBollingerBands(symbolObj, prices, period, multiplier) {
  if (prices.length < period) {
    return null; // Not enough data to calculate Bollinger Bands
  }

  const middleBand = [];
  const upperBand = [];
  const lowerBand = [];
  const breakouts = []; // Array to store breakout alerts

  // Calculate the initial EMA (exponential moving average) as the SMA for the first 'period' data points
  let sum = 0;
  for (let i = 0; i < period; i++) {
    sum += prices[i];
  }
  const initialEMA = sum / period;

  // Use the initial EMA as the first middle band value
  middleBand.push(initialEMA);

  for (let i = period; i < prices.length; i++) {
    const multiplierEMA = 2 / (period + 1);

    // Calculate the EMA for the current price
    const ema = (prices[i] - middleBand[i - period]) * multiplierEMA + middleBand[i - period];
    middleBand.push(ema);

    // Calculate the standard deviation for the period
    const stdDev = Math.sqrt(
      prices
        .slice(i - period + 1, i + 1)
        .map((price) => Math.pow(price - ema, 2))
        .reduce((a, b) => a + b, 0) / period
    );

    // Calculate the upper and lower bands
    upperBand.push(ema + multiplier * stdDev);
    lowerBand.push(ema - multiplier * stdDev);

    // Check for breakout conditions
    if (i > period) {
      if (prices[i] > upperBand[i - period] && prices[i - 1] <= upperBand[i - period - 1]) {
        // Breakout above the upper band
        breakouts.push('Breakout above upper band at index ' + i);
        console.log('Breakout above upper band at index ' + symbolObj[i]);
      } else if (prices[i] < lowerBand[i - period] && prices[i - 1] >= lowerBand[i - period - 1]) {
        // Breakout below the lower band
        breakouts.push('Breakout below lower band at index ' + i);
        console.log('Breakout below lower band at index ' + symbolObj[i]);
      }
    }
  }

  return { middleBand, upperBand, lowerBand, breakouts };
}

// Example usage
let stockPrices = [100, 105, 110, 115, 120, 125, 120, 115, 110, 105, 130, 135, 140];
const period = 20; // Number of days to calculate the moving average
const multiplier = 2; // Multiplier for standard deviation

const updateInterval = 60000; // 1 minute in milliseconds

function updateBollingerBands() {

  // stockPrices.push(/* Add new real-time price here */);
  let symbolObjKeys = findLocalStorageKeysStartingWith(`_minutes_`);
  symbolObjKeys.forEach(key => {
    let symbol = key.replace('_minutes_','');
    let symbolObj = getObjectFromLocalStorage(key);
    console.log(`Update price and calculate bollinger band for ${symbol}`);
    // [time,open,high,low,close,volume,0]
    // ele[1] is open price
    let pricecount = symbolObj.length;
    let symbolObj40 = symbolObj.filter(ele => { pricecount--; return pricecount <= (period*2); });
    stockPrices = symbolObj40.map(ele => ele[1]);
    console.log(`${symbol} `,stockPrices);
    // Recalculate Bollinger Bands with the updated data
    const bands = calculateBollingerBands(symbolObj40, stockPrices, period, multiplier);
    // console.log(symbol, " Middle Band:", bands.middleBand);
    // console.log(symbol, " Upper Band:", bands.upperBand);
    // console.log(symbol, " Lower Band:", bands.lowerBand);
    console.log(symbol, " Breakouts:", bands.breakouts);
  });
}

// Set up the interval to update Bollinger Bands
// const bollingerBandsInterval = setInterval(updateBollingerBands, updateInterval);




// ================================================================================
// ======================================== Monitoring ========================================
// ================================================================================

// TODO: fix start monitoring and setBuyingStopLossPrice()
// This is supposed to run only once for every symbol(symbolCode).
function startMonitoringPrice() {

  let instruments = getAllInstruments();// document.querySelectorAll('.instruments .instrument');

  for (var i=0 ;i<instruments.length;++i) {
    let instrumentContainer = instruments[i];
    let instrumentSymbol = instrumentContainer.querySelector('.nice-name').textContent;
    // stopLoss[symbol] = { active: 1, stop_loss: 0, hit: 0, initialized: 0, trail_by: 0.4 };
    const observer = new MutationObserver(handlePriceChangeAtZeroSec);
    const span = instrumentContainer.querySelector('.last-price');
    // Configure the observer to watch for changes in the span's content
    const config = { childList: true, characterData: true, subtree: true };
    observer.observe(span, config);
    
  }

}

// function symbolTransactionType(symbolObj){
//   if(!symbolObj) return false;
//   return symbolObj.transaction_type;
// }

// Function to handle changes in the span's content
async function handlePriceChangeAtZeroSec(mutationsList, observer) {
  for (const mutation of mutationsList) {
    // Find the parent div containing both nice-name and last-price
    const parentDiv = mutation.target.parentElement.parentElement.parentElement.parentElement;
    // let cur_instrument = parentDiv.querySelector('.nice-name');

    const symbol = parentDiv.querySelector('.nice-name').textContent;
    // console.log(`Mutation for `,symbol, mutation);
    if (mutation.type === 'characterData' || mutation.type === 'childList') {
      // Extract the instrument name and last price from the parent div
      const lastPrice = Number(mutation.target.textContent.trim());
      // let symbolCodes  = findLocalStorageKeysStartingWith(`trail_${symbol}`);
      let timeAtZero = getTimeStamp(null, null, 0);
      symbolOpenPrice[symbol] = { timeAtZero: lastPrice };
    }
  }
};


function runEveryMinuteOnZeroSeconds() {
  const now = new Date();
  const seconds = now.getSeconds();
  const millisecondsToWait = 60 * 1000 - seconds * 1000;

  console.log(` ======================= Wait ${millisecondsToWait/1000} seconds ====================`);
  setTimeout(() => {
    // Your code to run every minute on 0 seconds goes here
    // console.log('Running code every minute on 0 seconds');
    updateBollingerBands();

    // Call the function again to keep it running every minute
    runEveryMinuteOnZeroSeconds();
  }, millisecondsToWait);
}


// ================================================================================
// ======================================== API Calls ===============================
// ================================================================================

async function getApiData(symbolObj){
  
  console.log(`Fetch minutes Data for ${symbolObj.symbol}`);
  var requestOptions = {
    method: 'GET',
    headers: setHeaders(),
    redirect: 'follow'
  };
  
  fetch("https://kite.zerodha.com/oms/instruments/historical/"+symbolObj.id+"/minute?user_id=XX3150&oi=1&from=2023-11-07&to=2023-11-07", requestOptions)
    .then(response => response.text())
    .then(json => JSON.parse(json))
    .then(result => {
      // console.log(result);
      // set local variable 
      // [time,open,high,low,close,volume,0]
      console.log(`SET minutes Data for ${symbolObj.symbol}`);
      setObjectInLocalStorage(`_minutes_${symbolObj.symbol}`, result.data.candles);
    })
    .catch(error => console.log('error', error));
}
