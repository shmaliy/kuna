<?php

/* @var $this yii\web\View */
/* @var $data array */
/* @var $iteration array */
/* @var $params array */

$this->title = 'Market overview';
?>
<div class="site-index">
    
    <div class="body-content">
        
        <div class="row">
            <div class="col-md-8 col-sm-12">
                <h1>Eth/Uah chart</h1>
                <div id="chart" class="chart" style="height: 470px; width: 100%;"></div>
            </div>
            <div class="col-md-4 col-sm-12" id="overview">
                <h1>Bot Overview</h1>
                <div class="alert server-status" role="alert"></div>
                
                <h4>Trading status</h4>

                <div class="btn-group btn-group-lg" role="group" aria-label="trading_status">
                    <button type="button" class="btn btn-default" id="enTrade" data-action="1">Trade</button>
                    <button type="button" class="btn btn-default" id="enWatch" data-action="0">Watch</button>
                </div>
                
                
                <h4>Operations</h4>
                <div class="btn-group btn-group-lg" role="group" aria-label="manual_actions">
                    <button type="button" class="btn btn-warning" id="buyNow">Buy now</button>
                    <button type="button" class="btn btn-success" id="sellNow">Sell now</button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-1" id="trades">
                <h1>Trades</h1>
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <td>Price</td>
                            <td><?=$iteration['trades']['price']; ?></td>
                        </tr>
                        <tr>
                            <td>Volume</td>
                            <td><?=$iteration['trades']['volume']; ?></td>
                        </tr>
                        <tr>
                            <td>Side</td>
                            <td><?=$iteration['trades']['side']; ?></td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td><?=$iteration['trades']['created_at']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-3 col-sm-1" id="orders">
                <h1>Orders</h1>
                <?php if (!empty($iteration['orders'])) : ?>
                    <?php foreach ($iteration['orders'] as $order) : ?>
                    <table class="table table-striped">
                        <tbody>
                        <tr>
                            <td>Price</td>
                            <td><?=$order['price']; ?></td>
                        </tr>
                        <tr>
                            <td>Volume</td>
                            <td><?=$order['volume']; ?></td>
                        </tr>
                        <tr>
                            <td>Side</td>
                            <td><?=$order['side']; ?></td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td><?=$order['created_at']; ?></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="col-md-4 col-sm-1" id="balance">
                <h1>Balance</h1>
            </div>
            <div class="col-md-2 col-sm-1" id="params">
                <h1>Trading params</h1>
                <form method="post" id="paramsForm">
                    <?php foreach (\app\models\Param::$_formList as $name) : ?>
                        <?php foreach ($params as $param) : ?>
                            <?php if($param['name'] == $name) : ?>
                                <div class="form-group">
                                    <label for="<?=$param['name'] ?>"><?=\app\models\Param::$_names[$param['name']] ?></label>
                                    <input type="text" class="form-control form-control-sm"
                                           name="<?=$param['name'] ?>" id="<?=$param['name'] ?>"
                                           value="<?=$param['value'] ?>">
                                    <small id="<?=$param['name'] ?>Help" class="form-text text-muted"></small>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {

//        var chartData = {
//            animationEnabled: true,
//            theme: "dark2",
//            title:{
//                text: "<?php //echo end($data)['last']; ?>//"
//            },
//            axisX:{
//                valueFormatString: "HH:mm:ss"
//            },
//            <?php
//            $min = end($data)['last'];
//            foreach ($data as $l) {
//                if ($l['last'] < $min) $min = $l['last'];
//            }
//            ?>
//            axisY: {
//                title: "Rate",
//                suffix: "UAH",
//                minimum: <?php //echo $min; ?>
//            },
//            toolTip:{
//                shared:true
//            },
//            legend:{
//                cursor:"pointer",
//                verticalAlign: "bottom",
//                horizontalAlign: "left",
//                dockInsidePlotArea: true,
//                itemclick: toogleDataSeries
//            },
//            data: [
//                {
//                    type: "line",
//                    showInLegend: true,
//                    name: "Price",
//                    color: "#ff6600",
//                    yValueFormatString: "#",
//
//                    dataPoints: [
//                        <?php //foreach ($data as $i=>$r): ?>
//                        { x: new Date(<?php //echo $r['timestamp']*1000; ?>//), y: <?php //echo $r['last']; ?>// }<?php //if ($i-1 < count($data)) echo ", "; ?>
<!--                        --><?php //endforeach; ?>
//                    ]
//                }
//            ]
//        };
//        $('.chart').CanvasJSChart(chartData);
//
//        function toogleDataSeries(e){
//            if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
//                e.dataSeries.visible = false;
//            } else{
//                e.dataSeries.visible = true;
//            }
//            e.chart.render();
//        }
        
        function onloadInit()
        {
            $.ajax('/site/ticker', {
                method : 'POST',
                success : function (data_) {
                    data_ = $.parseJSON(data_);

                    google.charts.load('current', {'packages':['corechart']});
                    google.charts.setOnLoadCallback(drawChart);

                    function drawChart() {
                        var dataTable = [['Rate', 'Time']];
                        
                        $.each(data_.charts, function(k, v) {
                            dataTable.push([v.last, v.time]);
                        });
                        
                        var data = google.visualization.arrayToDataTable(dataTable);

                        var options = {
                            title: 'Company Performance',
                            curveType: 'function',
                            legend: { position: 'bottom' }
                        };

                        var chart = new google.visualization.LineChart(document.getElementById('chart'));

                        chart.draw(data, options);
                    }
                    
                    
                    console.log(data_);
//                    var chartData = {
//                        animationEnabled: false,
//                        theme: "dark2",
//                        title:{
//                            text: data.curr
//                        },
//                        axisX:{
//                            valueFormatString: "HH:mm:ss"
//                        },
//                        <?php
//                        $min = end($data)['last'];
//                        foreach ($data as $l) {
//                            if ($l['last'] < $min) $min = $l['last'];
//                        }
//                        ?>
//                        axisY: {
//                            title: "Rate",
//                            suffix: "UAH",
//                            minimum: data.min
//                        },
//                        toolTip:{
//                            shared:true
//                        },
//                        legend:{
//                            cursor:"pointer",
//                            verticalAlign: "bottom",
//                            horizontalAlign: "left",
//                            dockInsidePlotArea: true,
//                            itemclick: toogleDataSeries
//                        },
//                        data: [
//                            {
//                                type: "line",
//                                showInLegend: true,
//                                name: "Price",
//                                color: "#ff6600",
//                                yValueFormatString: "#",
//
//                                dataPoints: [
//
//                                ]
//                            }
//                        ]
//                    };
//
//                    $.each(data.charts, function(k, v) {
//                        chartData.data[0].dataPoints[chartData.data[0].dataPoints.length] = {
//                            x : new Date(parseInt(v.timestamp)*1000),
//                            y : parseInt(v.last)
//                        };
//                    });
//                    $('.chart').CanvasJSChart(chartData);
                    Interface.construct({data : data_});
                }
            });
        }
        onloadInit();
        
        setInterval(function(){
            onloadInit();
        }, 5000);
        
        var Interface = {
            data : {},
            overview : $('#overview'),
            trades : $('#trades'),
            orders : $('#orders'),
            balance : $('#balance'),
            params : $('#params'),
            iterationTimeout : parseInt($('#iterationTimeout').val()),
            construct : function (params) {
                var t = this;
                $.each(params, function (k, v) {
                    t[k] = v;
                });
                t.init();
            },
            init : function () {
                this.renderOverview();
                this.observeParams();
            },
            getParam : function(name) {
                var ret = null;
                $.each(this.data.params, function (k, v) {
                   if (v.name == name) ret =  v.value;
                });
                return ret;
            },
            renderOverview : function () {
                var lastIterationTime = new Date(parseInt(this.data.iteration.serverTs) * 1000);
                var timeout = parseInt(new Date(new Date().getTime() - lastIterationTime.getTime()).getTime()/1000);
                if (timeout > this.iterationTimeout) {
                    this.overview.find('.server-status')
                        .removeClass('alert-danger')
                        .removeClass('alert-success')
                        .text('')
                        .addClass('alert-danger').text('Last iteration was ' + timeout + ' seconds ago');
                } else {
                    this.overview.find('.server-status')
                        .removeClass('alert-danger')
                        .removeClass('alert-success')
                        .text('')
                        .addClass('alert-success').text('Last iteration was ' + timeout + ' seconds ago');
                }
                if (parseInt(this.getParam('trading')) == 1) {
                    $('#enTrade').removeClass('btn-default').addClass('btn-success');
                    $('#enWatch').removeClass('btn-success').addClass('btn-default');
                } else {
                    $('#enTrade').removeClass('btn-success').addClass('btn-default');
                    $('#enWatch').removeClass('btn-default').addClass('btn-success');
                }
                
                var t = this;
                
                $('#enTrade, #enWatch').unbind().click(function (e) {
                    e.preventDefault();
                    $.ajax({
                        url : '/site/toggle-trade',
                        method : 'POST',
                        data : {trading : $(this).attr('data-action')},
                        success : function(data) {
                            data = $.parseJSON(data);
                            if (!data.authorized) return t.ifNotAuth();
                            
                            if (data.trading == 0) {
                                $('#enTrade').removeClass('btn-default').addClass('btn-success');
                                $('#enWatch').removeClass('btn-success').addClass('btn-default');
                            } else {
                                $('#enWatch').removeClass('btn-default').addClass('btn-success');
                                $('#enTrade').removeClass('btn-success').addClass('btn-default');
                            }
                        }
                    });
                });
                
            },
            renderTrades : function () {
            
            },
            renderOrders : function () {
            
            },
            renderBalance : function () {
            
            },
            renderParams : function () {
            
            },
            observeParams : function () {
                $('#paramsForm').unbind().submit(function(e) {
                    e.preventDefault();
                    var data = {};
                    $.each($(this).find('input'), function(k, field) {
                        data[$(field).attr('name')] = $(field).val();
                        
                    });
                    $.ajax({
                        url : '/site/save-params',
                        data : data,
                        method : 'POST',
                        success : function (data) {
                            data = $.parseJSON(data);
                            if (!data.authorized) return t.ifNotAuth();
                        }
                    });
                });
            },
            ifNotAuth : function ()
            {
                window.location.href = '/site/login';
            }
            
        };
    });
</script>
<script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
<script src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script>