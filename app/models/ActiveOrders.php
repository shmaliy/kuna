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
            [['trades'], 'default', 'value' => self::getTradingHistory()],
            [['ticker'], 'default', 'value' => self::getTicker()],
            
            [['me', 'orders', 'trades', 'ticker', 'targetAccounts'], 'ifArray'],
            [['mainCurrencyBalance', 'quotCurrencyBalance', 'priceDiff'], 'number'],
            [['me'], 'canITrade'],
            [['trades'], 'getLastTrade'],
            [['tradeRecommendation'], 'in', 'range' => [self::D_BUY_NOW, self::D_BUY, self::D_WAIT,
                self::D_SELL, self::D_SELL_NOW]],
            [['now', 'me', 'trades', 'ticker'], 'required'],
        ];
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
                        if ($key == self::K_MAIN_CURRENCY) {
                            $this->mainCurrencyBalance = $val['balance'];
                        }
                        if ($key == self::K_QUOT_CURRENCY) {
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
            $this->orders = self::getApiClient()->signed()->activeOrders(self::K_DEFAULT_MARKET);
        } else {
            $this->orders = [];
        }
        return $this;
    }
    
    public static function getTradingHistory()
    {
        try {
            return self::getApiClient()->signed()->myHistory(self::K_DEFAULT_MARKET);
        } catch (ClientException $e) {
            var_export($e->getMessage());
            return null;
        }
    }
    
    public static function getTicker($market = self::K_DEFAULT_MARKET)
    {
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
            if ($orderLifetime >= 600 && $order['price'] != $this->ticker['ticker']['last']) {
                self::getApiClient()->signed()->cancelOrder($order['id']);
                echo "\t\t[v]\t Order killed\n";
            } else {
                echo "\t\t[-] Keep kalm and dream about maldives";
            }
        } else {
            echo "\t\tIt's sell order, his lifetime is ", date("H:i:s", $orderLifetime), "\n";
            if ($orderLifetime >= 1200 && $order['price'] > $this->ticker['ticker']['last']) {
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
        $desiredPrice = $minPrice + self::MIN_SELL_LEVEL;
        
        echo "\t\t[>]\t Need to sell ", self::K_MAIN_CURRENCY, "\n";
        echo "\t\t Last bid has price: \t", $this->trades['price'], "\n";
        echo "\t\t Last bid pause: \t", date("d H:i:s", $tradesPause), "\n";
        echo "\t\t Current ticker price: \t", $this->ticker['ticker']['last'], "\n";
        echo "\t\t Minimum desired price: \t", $desiredPrice, " (" , $desiredPrice - 10, ") ", "\n";
        
        if ($desiredPrice > $currPrice && $desiredPrice - $currPrice > 10 ) {
            echo "\t\t[x] Sell impossible\n";
            return;
        } else {
            if ($this->priceDiff > 0) {
                echo "\t\t[-] Price is growing. Wait...\n";
                return;
            } else {
                try {
                    $res = self::getApiClient()->signed()->createSellOrder(
                        self::K_DEFAULT_MARKET,
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
            
        }
    }
    
    public function tryToBuy()
    {
        echo "\t\t [>] Maybe time to buy? \n";
        if ($this->tradeRecommendation == self::D_BUY_NOW || $this->tradeRecommendation == self::D_BUY) {
        
            $volume = round(($this->quotCurrencyBalance - 10)/$this->ticker['ticker']['last'], 5);
            echo "\t\t ->\t Trying to buy $volume ", self::K_MAIN_CURRENCY, "\n";
            try {
                $res = self::getApiClient()->signed()->createBuyOrder(
                    self::K_DEFAULT_MARKET,
                    round(($this->quotCurrencyBalance - 10)/$this->ticker['ticker']['last'], 5, PHP_ROUND_HALF_DOWN),
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
        
        if ($prevValue != $this->quotCurrencyBalance) {
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
        echo "\tBase currency '", self::K_MAIN_CURRENCY, "' balance is ",
            $this->mainCurrencyBalance, "\tin orders - ", $this->targetAccounts[self::K_MAIN_CURRENCY]['locked'],
        "\ttotal = ", $this->mainCurrencyBalance + $this->targetAccounts[self::K_MAIN_CURRENCY]['locked'], "\n";
        echo "\tQuot currency '", self::K_QUOT_CURRENCY, "' balance is ",
            $this->quotCurrencyBalance, "\tin orders - ", $this->targetAccounts[self::K_QUOT_CURRENCY]['locked'],
        "\ttotal = ", $this->quotCurrencyBalance + $this->targetAccounts[self::K_QUOT_CURRENCY]['locked'], "\n";
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
            $seek->tickerMarket = self::K_DEFAULT_MARKET;
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
    
            if ( $position <= 25 && $this->priceDiff < 0 ) {
                echo " WAIT, price is going down \n";
                $this->tradeRecommendation = self::D_WAIT;
            }
            
            if ( $position <= 25 && $this->priceDiff >= 0 ) {
                echo " BUY!!!! \n";
                $this->tradeRecommendation = self::D_BUY_NOW;
            }
            
            
            if ( $position <= 40 && $position > 25  && $this->priceDiff < 0 ) {
                echo " WAIT, price is going down \n";
                $this->tradeRecommendation = self::D_WAIT;
            }
            if ( $position <= 40 && $position > 25 && $this->priceDiff >= 0 ) {
                echo " BUY or WAIT \n";
                $this->tradeRecommendation = self::D_BUY;
            }
            
            
            if ( $position <= 60 && $position > 40 ) {
                echo " WAIT\n";
                $this->tradeRecommendation = self::D_WAIT;
            }
            
            
            if ( $position <= 90 && $position > 60 && $this->priceDiff > 0) {
                echo " WAIT, price is rising \n";
                $this->tradeRecommendation = self::D_WAIT;
            }
            
            if ( $position <= 90 && $position > 60 && $this->priceDiff <= 0) {
                echo " SELL \n";
                $this->tradeRecommendation = self::D_SELL;
            }
            
            
            if ( $position <= 100 && $position > 90 && $this->priceDiff > 0) {
                echo " WAIT, price is rising \n";
                $this->tradeRecommendation = self::D_WAIT;
            }
            
            if ( $position <= 100 && $position > 90 && $this->priceDiff <= 0) {
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
        if ($trade->validate()) {
            $trade->getActiveOrders()->whatsToDo();
        } else {
            var_export($trade->getErrors());
        }
        
        echo "\n###########################################\n\n\n";
        sleep(10);
        self::trade($userId);
    }
}
