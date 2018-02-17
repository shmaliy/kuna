<?php

namespace app\models;

use madmis\KunaApi\Exception\ClientException;
use madmis\KunaApi\KunaApi;
use Yii;

/**
 * This is the model class for table "{{%active_orders}}".
 *
 * @property int $id
 * @property string $side
 * @property string $ord_type
 * @property double $price
 * @property double $avg_price
 * @property string $state
 * @property string $market
 * @property string $created_at
 * @property double $volume
 * @property double $remaining_volume
 * @property double $executed_volume
 * @property double $trades_count
 *
 * @property double $mainCurrencyBalance
 * @property double $quotCurrencyBalance
 *
 * @property int $now
 * @property int $userId
 * @property array $me
 * @property array $orders
 * @property array $trades
 * @property array $ticker
 * @property array $targetAccounts
 * @property boolean $hasActiveOrders
 * @property integer $tradeRecommendation
 * @property double $priceDiff
 *
 * @property string $param_market
 * @property string $param_baseCurrency
 * @property string $param_quotCurrency
 * @property int $param_iterationTimeout
 * @property int $param_minSellLevel
 * @property int $param_sellLevel
 * @property int $param_trading
 * @property int $param_buyNow
 * @property int $param_buy
 * @property int $param_wait
 * @property int $param_sell
 * @property int $param_sellNow
 * @property int $param_sellOrderNow
 * @property int $param_buyOrderNow
 * @property int $param_buyOrderLifetime
 * @property int $param_sellOrderLifetime
 */
class ActiveOrders extends \yii\db\ActiveRecord
{
    const K_MAIN_CURRENCY = 'eth';
    const K_QUOT_CURRENCY = 'uah';
    const K_DEFAULT_MARKET = MarketSeek::MARKET_ethuah;
    
    const TRADE_MAIN = 'bid'; // Была приобретена криптовалюта
    const TRADE_QUOT = 'ask'; // Была приобретена гривна
    
    const ORDER_SIDE_BUY = 'buy';
    const ORDER_SIDE_SELL = 'sell';
    
    const MIN_SELL_LEVEL = 500;
    const MIN_BUY_LEVEL = 300;
    
    const D_BUY_NOW = 1;
    const D_BUY = 2;
    const D_WAIT = 3;
    const D_SELL = 4;
    const D_SELL_NOW = 5;
    
    public $userId;
    public $now;
    public $priceDiff;
    public $me;
    public $mainCurrencyBalance, $quotCurrencyBalance;
    public $orders, $trades;
    public $ticker;
    public $targetAccounts;
    public $hasActiveOrders = false;
    public $tradeRecommendation = self::D_WAIT;
    
    
    // params
    public $param_market;
    public $param_baseCurrency;
    public $param_quotCurrency;
    public $param_iterationTimeout;
    public $param_minSellLevel;
    public $param_sellLevel;
    public $param_trading;
    public $param_buyNow;
    public $param_buy;
    public $param_wait;
    public $param_sell;
    public $param_sellNow;
    public $param_sellOrderNow;
    public $param_buyOrderNow;
    public $param_buyOrderLifetime;
    public $param_sellOrderLifetime;
    
    public static $accountsKey = 'accounts';
    public static $accountMap = [
        'currency', 'balance', 'locked'
    ];
    public static $targetAccountKeys = [self::K_MAIN_CURRENCY, self::K_QUOT_CURRENCY];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%active_orders}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['side', 'ord_type', 'market'], 'string'],
            [['price', 'avg_price', 'volume', 'remaining_volume', 'executed_volume', 'trades_count'], 'number'],
            [['state', 'created_at'], 'string', 'max' => 50],
            
            [['now'], 'integer', 'min' => 0],
            [['hasActiveOrders'], 'boolean'],
            [['userId'], 'integer'],
            [['userId'], 'required'],
            [['userId'], 'exist', 'targetClass' => User::className(), 'targetAttribute' => 'id'],
            [['now'], 'default', 'value' => self::getNow()],
            [['me'], 'default', 'value' => self::me()],
//            [['orders'], 'default', 'value' => self::getActiveOrders()],
            [['trades'], 'default', 'value' => $this->getTradingHistory()],
            [['ticker'], 'default', 'value' => $this->getTicker()],
            
            [['me', 'orders', 'trades', 'ticker', 'targetAccounts'], 'ifArray'],
            [['mainCurrencyBalance', 'quotCurrencyBalance', 'priceDiff'], 'number'],
            [['me'], 'canITrade'],
            [['trades'], 'getLastTrade'],
            [['tradeRecommendation'], 'in', 'range' => [self::D_BUY_NOW, self::D_BUY, self::D_WAIT,
                self::D_SELL, self::D_SELL_NOW]],
            [['now', 'me', 'trades', 'ticker'], 'required'],
            [['param_market'], 'in', 'range' => MarketSeek::$_markets],
            [['param_market', 'param_baseCurrency', 'param_quotCurrency', 'param_iterationTimeout',
                'param_minSellLevel', 'param_sellLevel', 'param_trading', 'param_buyNow',
                'param_buy', 'param_wait', 'param_sell', 'param_sellNow', 'param_sellOrderNow',
                'param_buyOrderNow', 'param_buyOrderLifetime', 'param_sellOrderLifetime'], 'string'],
            [['param_market', 'param_baseCurrency', 'param_quotCurrency', 'param_iterationTimeout',
                'param_minSellLevel', 'param_sellLevel', 'param_trading', 'param_buyNow',
                'param_buy', 'param_wait', 'param_sell', 'param_sellNow', 'param_sellOrderNow',
                'param_buyOrderNow', 'param_buyOrderLifetime', 'param_sellOrderLifetime'], 'required'],
        ];
    }
    
    public function setParams()
    {
        $params = new Param();
        $params->userId = $this->userId;
        $params = $params->getParams();
        
        foreach ($params as $param) {
            $this->{'param_'.$param['name']} = $param['value'];
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'side' => 'Side',
            'ord_type' => 'Ord Type',
            'price' => 'Price',
            'avg_price' => 'Avg Price',
            'state' => 'State',
            'market' => 'Market',
            'created_at' => 'Created At',
            'volume' => 'Volume',
            'remaining_volume' => 'Remaining Volume',
            'executed_volume' => 'Executed Volume',
            'trades_count' => 'Trades Count',
        ];
    }
    
    public function ifArray($attribute)
    {
        if (!is_null($this->$attribute) && !is_array($this->$attribute)) {
            $this->addError($attribute, 'should be array or null');
        }
    }
    
    public function canItrade()
    {
        if (empty($this->me) || !isset($this->me['activated']) || !$this->me['activated']) {
            $this->addError('me', 'Can\'t receive account info');
        } else {
            $this->fillTargetAccounts('targetAccounts');
        }
    }
    
    public function fillTargetAccounts($attribute)
    {
        if (!empty($this->me['accounts'])) {
            foreach (self::$targetAccountKeys as $key) {
                foreach ($this->me['accounts'] as $val) {
                    if ($key == $val['currency']) {
                        $this->$attribute[$key] = $val;
                        if ($val['locked'] > 0) {
                            $this->hasActiveOrders = true;
                        }
                        if ($key == $this->param_baseCurrency) {
                            $this->mainCurrencyBalance = $val['balance'];
                        }
                        if ($key == $this->param_quotCurrency) {
                            $this->quotCurrencyBalance = $val['balance'];
                        }
                    }
                }
            }
            unset($this->me['accounts']);
        }
    }
    
    public function getLastTrade($attribute) {
        if (is_array($this->$attribute) && !empty($this->$attribute)) {
            $this->$attribute = reset($this->$attribute);
        }
    }

    /**
     * @inheritdoc
     * @return \app\models\query\ActiveOrdersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ActiveOrdersQuery(get_called_class());
    }
    
    /**
     * @return KunaApi
     */
    public static function getApiClient()
    {
        return new KunaApi(KUNA_URL, KUNA_PK, KUNA_SK);
    }
    
    public static function me()
    {
        try {
            return self::getApiClient()->signed()->me();
        } catch (ClientException $e) {
            var_export($e->getMessage());
            return null;
        }
    }
    
    
    public function getActiveOrders()
    {
        
        if ($this->hasActiveOrders) {
            
            try {
                $this->orders = self::getApiClient()->signed()->activeOrders($this->param_market);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $this->orders = [];
                var_export($e->getMessage());
            }
        } else {
            $this->orders = [];
        }
        return $this;
    }
    
    public function getTradingHistory()
    {
        try {
            return self::getApiClient()->signed()->myHistory($this->param_market);
        } catch (ClientException $e) {
            var_export($e->getMessage());
            return null;
        }
    }
    
    public function getTicker($market = null)
    {
        if (is_null($market)) $market = $this->param_market;
        try {
            return self::getApiClient()->shared()->tickers($market);
        } catch (ClientException $e) {
            var_export($e->getMessage());
            return null;
        }
    }
    
    public static function getNow()
    {
        try {
            return self::getApiClient()->shared()->timestamp();
        } catch (ClientException $e) {
            var_export($e->getMessage());
            return null;
        }
        
    }
    
    public function orderDesicion()
    {
        echo "\t\t[>]\t Let's look at order \n";
        
        var_export($this->orders[0]);
        echo "\n";
        
        $order = $this->orders[0];
        $orderLifetime = $this->now - strtotime($order['created_at']);
        if ($order['side'] == self::ORDER_SIDE_BUY) {
            
            echo "\t\tIt's buy order, his lifetime is ", date("H:i:s", $orderLifetime), "\n";
            if (
//                $orderLifetime >= $this->param_buyOrderLifetime
//                && $this->param_buyOrderLifetime > 0
//                &&
                $order['price'] != $this->ticker['ticker']['last']) {
                self::getApiClient()->signed()->cancelOrder($order['id']);
                echo "\t\t[v]\t Order killed\n";
            } else {
                echo "\t\t[-] Keep kalm and dream about maldives";
            }
        } else {
            echo "\t\tIt's sell order, his lifetime is ", date("H:i:s", $orderLifetime), "\n";
            if (
//                $orderLifetime >= $this->param_sellOrderLifetime
//                && $this->param_sellOrderLifetime > 0
//                &&
                $order['price'] > $this->ticker['ticker']['last']) {
                self::getApiClient()->signed()->cancelOrder($order['id']);
                echo "\t\t[v]\t Order killed\n";
            } else {
                echo "\t\t[-] Keep kalm and dream about maldives";
            }
        }
        
    }
    
    public function tryToSell()
    {
        $minPrice = $this->trades['price'];
        $currPrice = $this->ticker['ticker']['last'];
        
        $created = strtotime($this->trades['created_at']);
        $tradesPause = $this->now - $created;
        $desiredPrice = $minPrice + $this->param_sellLevel;
        $desiredPriceSmooth = $minPrice + $this->param_minSellLevel;
        
        echo "\t\t[>]\t Need to sell ", $this->param_baseCurrency, "\n";
        echo "\t\t Last bid has price: \t", $this->trades['price'], "\n";
        echo "\t\t Last bid pause: \t", date("d H:i:s", $tradesPause), "\n";
        echo "\t\t Current ticker price: \t", $this->ticker['ticker']['last'], "\n";
        echo "\t\t Minimum desired price: \t", $desiredPrice, " (" , $desiredPriceSmooth, ") ", "\n";
        
        if ($desiredPrice > $currPrice && $desiredPrice - $currPrice > $desiredPrice - $desiredPriceSmooth ) {
            echo "\t\t[x] Sell impossible\n";
            return;
        } else {
            if ($this->priceDiff > 0) {
                echo "\t\t[-] Price is growing. Wait...\n";
                return;
            } else {
                return $this->sell();
            }
            
        }
    }
    
    public function sell()
    {
        try {
            $res = self::getApiClient()->signed()->createSellOrder(
                $this->param_market,
                $this->mainCurrencyBalance,
                $this->ticker['ticker']['last']
            );
        } catch (ClientException $e) {
            var_export($e->getMessage());
            echo "\n\t\t[x] Fix your bugs, idiot!!! \n";
            return;
        }
    
        var_export($res);
    
        echo "\n\t\t[v] Sell order created...\n";
        return;
    }
    
    public function tryToBuy()
    {
        echo "\t\t [>] Maybe time to buy? \n";
        if ($this->tradeRecommendation == self::D_BUY_NOW || $this->tradeRecommendation == self::D_BUY) {
        
            $volume = round(($this->quotCurrencyBalance - 3)/$this->ticker['ticker']['last'], 5);
            echo "\t\t ->\t Trying to buy $volume ", $this->param_quotCurrency, "\n";
            try {
                $res = self::getApiClient()->signed()->createBuyOrder(
                    $this->param_market,
                    round(($this->quotCurrencyBalance - 3)/$this->ticker['ticker']['last'], 5, PHP_ROUND_HALF_DOWN),
                    $this->ticker['ticker']['last']
                );
            } catch (ClientException $e) {
                var_export($e->getMessage());
                echo "\n\t\t[x] Fix your bugs, idiot!!! \n";
                return;
            }
            var_export($res);
            echo "\t\t[v] Done \n";
        } else {
            echo "\t\t[x] Bad idea \n";
        }
    }
    
    public function tryToSave()
    {
    
    }
    
    public function saveBalance()
    {
        $prev = Balance::find()
            ->where('userId = :uid', [':uid' => $this->userId])
            ->orderBy('id desc')
            ->one();
        
        $prevValue = 0;
        if (!is_null($prev)) {
            $prevValue = $prev->balance;
        }
        
        if ($prevValue != round($this->quotCurrencyBalance, 2, 2)) {
            $curr = new Balance();
            $curr->userId = $this->userId;
            $curr->balance = $this->quotCurrencyBalance;
            if ($curr->validate()) {
                $curr->save();
            }
        }
    }
    
    public function whatsToDo()
    {
        $this->globalInfo();
        $this->commonTickerAnalisis();
        
        if ($this->param_trading != 1) {
            echo "\t\t [-] Bot in watch mode \t";
            return;
        }
        
        if ($this->hasActiveOrders) {
        
            $this->orderDesicion();
            return;
        }
//        return;
        
        if ($this->trades['side'] == self::TRADE_MAIN) {
            $this->tryToSell();
            return;
        }
        
        if ($this->trades['side'] == self::TRADE_QUOT) {
            $this->saveBalance();
            $this->tryToBuy();
            return;
        }
    }
    
    public function globalInfo()
    {
        echo "\tDate ", date("Y-m-d H:i:s", $this->now), "\n";
        echo "\tTrading account ", $this->me['email'], "\n";
        echo "\tBase currency '", $this->param_baseCurrency, "' balance is ",
            $this->mainCurrencyBalance, "\tin orders - ", $this->targetAccounts[$this->param_baseCurrency]['locked'],
        "\ttotal = ", $this->mainCurrencyBalance + $this->targetAccounts[$this->param_baseCurrency]['locked'], "\n";
        echo "\tQuot currency '", $this->param_quotCurrency, "' balance is ",
            $this->quotCurrencyBalance, "\tin orders - ", $this->targetAccounts[$this->param_quotCurrency]['locked'],
        "\ttotal = ", $this->quotCurrencyBalance + $this->targetAccounts[$this->param_quotCurrency]['locked'], "\n";
        echo "\n\n";
//        var_export($this);
//        echo "\n";
    
    }
    
    public function commonTickerAnalisis()
    {
        if (!empty($this->ticker)) {
//            var_export($this->ticker);
            $this->priceDiff = $this->ticker['ticker']['last'] - MarketSeek::getLastPrice();
            
            $seek = new MarketSeek();
            $seek->tickerMarket = $this->param_market;
            $seek->ticker = $this->ticker;
            
            if ($this->priceDiff != 0) {
                $seek->addTicker();
            }
    
            $min     = $this->ticker['ticker']['low'];
            $max     = $this->ticker['ticker']['high'];
            $current = $this->ticker['ticker']['last'];
    
            $diff  = $max - $min;
            $point = $current - $min;
    
            $position = round( $point / $diff * 100 );
    
            echo "\t\t Min price \t", $min, "\n";
            echo "\t\t Max price \t", $max, "\n";
            echo "\t\t Current price \t", $current, "\n";
            echo "\t\t Price diff \t", $this->priceDiff, "\n";
            echo "\t\t Price position \t", $position, "%\n";
    
            echo "\t\t[";
            for ( $i = 1; $i <= 100; $i ++ ) {
                if ( $i != $position ) {
                    echo '.';
                } else {
                    echo '|';
                }
            }
            echo "]\n\n";
            echo "\t\t GLOBAL RECOMMENDATION: ";
    
            if ( $position <= $this->param_buyNow && $this->priceDiff < 0 ) {
                echo " WAIT, price is going down \n";
                $this->tradeRecommendation = self::D_WAIT;
            }
            
            if ( $position <= $this->param_buyNow && $this->priceDiff >= 0 ) {
                echo " BUY!!!! \n";
                $this->tradeRecommendation = self::D_BUY_NOW;
            }
            
            
            if ( $position <= $this->param_buy && $position > $this->param_buyNow  && $this->priceDiff < 0 ) {
                echo " WAIT, price is going down \n";
                $this->tradeRecommendation = self::D_WAIT;
            }
            if ( $position <= $this->param_buy && $position > $this->param_buyNow && $this->priceDiff >= 0 ) {
                echo " BUY or WAIT \n";
                $this->tradeRecommendation = self::D_BUY;
            }
            
            
            if ( $position <= $this->param_wait && $position > $this->param_buy ) {
                echo " WAIT\n";
                $this->tradeRecommendation = self::D_WAIT;
            }
            
            
            if ( $position <= $this->param_sell && $position > $this->param_wait && $this->priceDiff > 0) {
                echo " WAIT, price is rising \n";
                $this->tradeRecommendation = self::D_WAIT;
            }
            
            if ( $position <= $this->param_sell && $position > $this->param_wait && $this->priceDiff <= 0) {
                echo " SELL \n";
                $this->tradeRecommendation = self::D_SELL;
            }
            
            
            if ( $position <= $this->param_sellNow && $position > $this->param_sell && $this->priceDiff > 0) {
                echo " WAIT, price is rising \n";
                $this->tradeRecommendation = self::D_WAIT;
            }
            
            if ( $position <= $this->param_sellNow && $position > $this->param_sell && $this->priceDiff <= 0) {
                echo " SELL!!!! \n";
                $this->tradeRecommendation = self::D_SELL_NOW;
            }
    
            echo "\n";
            Iterations::register($this->now, $this->userId, [
                'me' => json_encode($this->me),
                'priceDiff' => $this->priceDiff,
                'mainCurrencyBalance' => $this->mainCurrencyBalance,
                'quotCurrencyBalance' => $this->quotCurrencyBalance,
                'orders' => json_encode($this->orders),
                'trades' => json_encode($this->trades),
                'ticker' => json_encode($this->ticker),
                'targetAccounts' => json_encode($this->targetAccounts),
                'tradeRecommendation' =>$this->tradeRecommendation
            ]);
        }
    }
    
    public static function trade($userId)
    {
        echo "******** TRADING ITERATION ********* \n\n";
        
        $trade = new self;
        $trade->loadDefaultValues();
        $trade->userId = $userId;
        $trade->setParams();
        if ($trade->validate()) {
            $trade->getActiveOrders()->whatsToDo();
        } else {
            var_export($trade->getErrors());
        }
        
        echo "\n###########################################\n\n\n";
        sleep($trade->param_iterationTimeout);
        self::trade($userId);
    }
}
