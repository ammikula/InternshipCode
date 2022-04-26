<!--Php Graph Creation to String Function, returns String of script-->
<?php

function createGraphStr0($ChartData, $ChartType, $ChartDivName, $ChartTitle, $ChartWidth, $ChartHeight)
{
	$ChartTypeArray = explode("-",$ChartType);
	$BaseChartType = $ChartTypeArray[0];
	$OtherChartOptions = $ChartTypeArray[1];
	$ChartOptions = "title: '$ChartTitle', width: ".$ChartWidth.", height: ".$ChartHeight;
	// $ChartOptions = "title: '$ChartTitle', width: ".$ChartWidth.", height: ".$ChartHeight.", colors: ['#0F95FE','#2DFE0F','#0FFEF0','#FEF00F','#FEA30F', '#F81515']";
  $IsMoreOptions = true; //if true, PieChart: 3D, ColumnChart: stacked, LineChart: curved
  $ChartTypeFunction = 'draw' . $BaseChartType;

	// Adding on other option if not base.
    switch($OtherChartOptions)
    {
        case "Base":
		case "StackedTotal":
          	break;
        case "3D":
            $ChartOptions = $ChartOptions . ", is3D: true"; // 3D (PieChart, ColumnChart, BarChart).
            break;
		case "StackedBase":
            $ChartOptions = $ChartOptions . ", isStacked: true"; // Stacked (ColumnChart, BarChart).
            break;
        case "StackedPercent":
                $ChartOptions = $ChartOptions . ", isStacked: 'percent'"; // Stacked + Percent (ColumnChart, BarChart).
                break;
        case "NSPBase":
                $ChartOptions = $ChartOptions . ",
                series: {
                    0:{color:'#FF0000'}, //red
                    1:{color:'#FFFF00'}, //yellow
                    2:{color:'#00FF00'} //green
                }"; // NSP Coloring (ColumnChart, BarChart).
                break;
        case "NSPStacked":
                $ChartOptions = $ChartOptions . ", isStacked: true,
                series: {
                    0:{color:'#FF0000'}, //red
                    1:{color:'#FFFF00'}, //yellow
                    2:{color:'#00FF00'} //green
                }"; // Stacked + NSP Coloring (ColumnChart, BarChart).
                break;
        case "Curved":
            $ChartOptions = $ChartOptions . ", curveType: 'function'"; // Curved (LineChart).
            break;
		case "StackedAvg":
        case "ToolTip":
                $ChartOptions = $ChartOptions . ", isStacked: true, focusTarget: 'category', tooltip: {isHtml: true}"; // ToolTip (ColumnChart, BarChart)
                break;
         default:
            echo "OtherChartOptions not found: ".$OtherChartOptions;
            break;
    }

	$GraphStr = "
    <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
    <script type='text/javascript'>

      // Load charts and packages needed.
      google.charts.load('current', {'packages':['corechart']});

      // Draw chart when loaded.
      google.charts.setOnLoadCallback(".$ChartTypeFunction.");

			//function to create graph of choice
      function ".$ChartTypeFunction."() {
        // Create the data table.
				if('".$OtherChartOptions."' == 'StackedAvg'){
					var data = ".json_encode($ChartData).";
					var chart_data = new google.visualization.DataTable();
					chart_data.addColumn('string', data[0][0]);
					for(var i = 1; i < data[0].length; i++){
						chart_data.addColumn('number', data[0][i]);
						i++;
						chart_data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
					}
					for(var i = 1; i < data.length; i++){
						chart_data.addRow(data[i]);
					}
				} else {
					var chart_data = google.visualization.arrayToDataTable(".json_encode($ChartData).");
				}

        // Instantiate and draw chart with options and chart type included.
        var chart = new google.visualization.".$BaseChartType."(document.getElementById('".$ChartDivName."'));
        chart.draw(chart_data, {".$ChartOptions."});
      }
    </script>";
	return($GraphStr);
}

if (count($SelectChartArray) == 0)
	echo "Click the [Select Graphs] button to select questions to build question specific graphs. If you select a matrix questions, the sub items will be displayed in a stacked bar chart.";
else
{
    echo "<table>";
    for ($NumSelectChartArrayId = 0, $NumSimpleGraphs = 0; (($NumSelectChartArrayId < 100) && ($NumSelectChartArrayId < count($SelectChartArray))); $NumSelectChartArrayId++)
    {
		if ($SelectChartArray[$NumSelectChartArrayId]['SurveySelectGraphsTable']['QuestionType'] == "Matrix") $WideChart = true; else $WideChart = false;
        if ($WideChart or (($NumSimpleGraphs % 2) == 0)) echo "<tr>";
        $ChartName = "Chart".$NumSelectChartArrayId;
		echo createGraphStr0((array)$SelectChartArray[$NumSelectChartArrayId]['Rubric'], $SelectChartArray[$NumSelectChartArrayId]['SurveySelectGraphsTable']['GraphType'], $ChartName, HTMLQuoteConvert(strip_tags($SelectChartArray[$NumSelectChartArrayId]['SurveySelectGraphsTable']['question'])), ($WideChart?1135:500), ($WideChart?500:400));
		echo "<td ".($WideChart?"colspan = '2'":"")." ><div id='".$ChartName."' style='border: 1px solid #ccc'></div></td>";
       	if ($WideChart or ((++$NumSimpleGraphs % 2) == 0) or ($NumSelectChartArrayId == count($SelectChartArray))) echo "</tr>";
	}
	echo "</table>";
}


?>
