<?php

/* @var $this yii\web\View */
/* @var $data array */

$this->title = 'Market overview';
?>
<div class="site-index">
    <h1>Eth/Uah charts</h1>
    
    <script>
        window.onload = function () {

            var chartData = [];
            var optRow = {};
            <?php foreach ($data as $key=>$d) : ?>
            optRow = {
                animationEnabled: true,
                theme: "dark2",
                title:{
                    text: "<?php echo $key; ?>, <?php echo $d[count($d)-1]['buy']; ?>"
                },
                axisX:{
                    valueFormatString: "HH:mm:ss"
                },
                <?php
                    $mB = $d[0]['buy'];
                    $mS = $d[0]['sell'];
                    $min = 0;
                    foreach ($d as $l) {
                        if ($l['buy'] < $mB) $mB = $l['buy'];
                        if ($l['sell'] < $mS) $mS = $l['sell'];
                    }
                    
                    if ($mB >= $mS) {
                        $min = $mS;
                    } else {
                        $min = $mB;
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
                data: [{
                        type: "line",
                        showInLegend: true,
                        name: "Buy",
//                        markerType: "square",
                        lineDashType: "dash",
                        xValueFormatString: "HH:mm:ss",
                        color: "#cccc99",
                        yValueFormatString: "#",
                        dataPoints: [
                            <?php foreach ($d as $i=>$r): ?>
                            { x: new Date(<?php echo $r['timestamp']*1000; ?>), y: <?php echo $r['buy']; ?> }<?php if ($i-1 < count($d)) echo ", "; ?>
                            <?php endforeach; ?>
                        ]
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        name: "Sell",
                        color: "#ff6600",
                        yValueFormatString: "#",
                        
                        dataPoints: [
                            <?php foreach ($d as $i=>$r): ?>
                            { x: new Date(<?php echo $r['timestamp']*1000; ?>), y: <?php echo $r['sell']; ?> }<?php if ($i-1 < count($d)) echo ", "; ?>
                            <?php endforeach; ?>
                        ]
                    },
                    {
                        type: "line",
                        showInLegend: true,
                        lineDashType: "dash",
                        name: "Median",
                        markerType: "square",
                        yValueFormatString: "#",
                        color: "#cccccc",
                        dataPoints: [
                            <?php foreach ($d as $i=>$r): ?>
                            { x: new Date(<?php echo $r['timestamp']*1000; ?>), y: <?php echo ($r['sell']+$r['buy'])/2; ?> }<?php if ($i-1 < count($d)) echo ", "; ?>
                            <?php endforeach; ?>
                        ]
                    }
                ]
            };
            chartData.push(optRow);
            <?php endforeach; ?>
            $.each ($('.chart'), function (k, v) {
               $(v).CanvasJSChart(chartData[k]);
            });

            function toogleDataSeries(e){
                if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                } else{
                    e.dataSeries.visible = true;
                }
                e.chart.render();
            }
            setInterval(function(){
                location.reload()
            }, 30000);
        }
    </script>
    

    <div class="body-content">
        
        <?php foreach ($data as $key=>$d) :?>
            <div class="chart" style="height: 470px; width: 100%;"></div>
            <br />
            <br />
        <?php endforeach; ?>
        
        <script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
        <script src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script>
    </div>
</div>
