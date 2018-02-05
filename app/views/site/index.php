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
                <div class="chart" style="height: 470px; width: 100%;"></div>
            </div>
            <div class="col-md-4 col-sm-12">
                <h1>Bot Overview</h1>
                
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-1">
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
            <div class="col-md-3 col-sm-1">
                <h1>Orders</h1>
                <?php if (!empty($iteration['orders'])) : ?>
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
                <?php endif; ?>
            </div>
            <div class="col-md-4 col-sm-1">
                <h1>Balance</h1>
            </div>
            <div class="col-md-2 col-sm-1">
                <h1>Trading params</h1>
                <form method="post">
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

        var chartData = {
            animationEnabled: true,
            theme: "dark2",
            title:{
                text: "<?php echo end($data)['last']; ?>"
            },
            axisX:{
                valueFormatString: "HH:mm:ss"
            },
            <?php
            $min = end($data)['last'];
            foreach ($data as $l) {
                if ($l['last'] < $min) $min = $l['last'];
            }
            ?>
            axisY: {
                title: "Rate",
                suffix: "UAH",
                minimum: <?php echo $min; ?>
            },
            toolTip:{
                shared:true
            },
            legend:{
                cursor:"pointer",
                verticalAlign: "bottom",
                horizontalAlign: "left",
                dockInsidePlotArea: true,
                itemclick: toogleDataSeries
            },
            data: [
                {
                    type: "line",
                    showInLegend: true,
                    name: "Price",
                    color: "#ff6600",
                    yValueFormatString: "#",

                    dataPoints: [
                        <?php foreach ($data as $i=>$r): ?>
                        { x: new Date(<?php echo $r['timestamp']*1000; ?>), y: <?php echo $r['last']; ?> }<?php if ($i-1 < count($data)) echo ", "; ?>
                        <?php endforeach; ?>
                    ]
                }
            ]
        };
        $('.chart').CanvasJSChart(chartData);

        function toogleDataSeries(e){
            if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else{
                e.dataSeries.visible = true;
            }
            e.chart.render();
        }
        
        setInterval(function(){
            $.ajax('/site/ticker', {
                method : 'POST',
                success : function (data) {
                    data = $.parseJSON(data);
                    console.log(data);
                    var chartData = {
                        animationEnabled: false,
                        theme: "dark2",
                        title:{
                            text: data.curr
                        },
                        axisX:{
                            valueFormatString: "HH:mm:ss"
                        },
                        <?php
                        $min = end($data)['last'];
                        foreach ($data as $l) {
                            if ($l['last'] < $min) $min = $l['last'];
                        }
                        ?>
                        axisY: {
                            title: "Rate",
                            suffix: "UAH",
                            minimum: data.min
                        },
                        toolTip:{
                            shared:true
                        },
                        legend:{
                            cursor:"pointer",
                            verticalAlign: "bottom",
                            horizontalAlign: "left",
                            dockInsidePlotArea: true,
                            itemclick: toogleDataSeries
                        },
                        data: [
                            {
                                type: "line",
                                showInLegend: true,
                                name: "Price",
                                color: "#ff6600",
                                yValueFormatString: "#",

                                dataPoints: [
                                
                                ]
                            }
                        ]
                    };
                    
                    $.each(data.charts, function(k, v) {
                        chartData.data[0].dataPoints[chartData.data[0].dataPoints.length] = {
                            x : new Date(parseInt(v.timestamp)*1000),
                            y : parseInt(v.last)
                        };
                    });
//                    console.log(chartData);

                    $('.chart').CanvasJSChart(chartData);
                }
            });
        }, 1000);
    });
</script>
<script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
<script src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script>