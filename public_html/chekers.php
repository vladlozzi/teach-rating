<?php
if(!defined("IN_ADMIN")) die;
function teachers($cnt) {
	switch (true) {
		case ($cnt % 10 == 1) and ($cnt % 100 != 11) : $str = "викладач"; break;
		case ($cnt % 10 == 2) and ($cnt % 100 != 12) : $str = "викладачі"; break;
		case ($cnt % 10 == 3) and ($cnt % 100 != 13) : $str = "викладачі"; break;
		case ($cnt % 10 == 4) and ($cnt % 100 != 14) : $str = "викладачі"; break;
		default: $str = "викладачів"; break;
	}
	
	return $str;
}

function selectCommonChecker($name, $query, $value, $previosValue, $view) {
global $conn;
$str = "<div class=\"content-selector\">";
$str .= "<select name=\"".$name."\" onchange=\"submit()\">";
			$str .= "<option></option>";
					$select_result=mysqli_query($conn, $query);
					while($select_row = mysqli_fetch_array($select_result))
						{
							if(stripslashes($select_row[$value])==$previosValue)
								{
									$str .= "<option selected value=\"";
								}
							else
								{
									$str .= "<option value=\"";
								}
							$str .= stripslashes($select_row[$value]);
							$str .= "\">";
							$str .= stripslashes($select_row[$view]);
							$str .= "</option>";
						}
$str .= "</select></div>";
return $str;
}

function selectCommonCheckerNoAutoSubmit($name, $query, $value, $previosValue, $view) {
global $conn;
$str = "<div class=\"content-selector\">";
$str .= "<select name=\"".$name."\">";
			$str .= "<option></option>";
					$select_result=mysqli_query($conn, $query);
					while($select_row = mysqli_fetch_array($select_result))
						{
							if(stripslashes($select_row[$value])==$previosValue)
								{
									$str .= "<option selected value=\"";
								}
							else
								{
									$str .= "<option value=\"";
								}
							$str .= stripslashes($select_row[$value]);
							$str .= "\">";
							$str .= stripslashes($select_row[$view]);
							$str .= "</option>";
						}
$str .= "</select></div>";
return $str;
}

function selectTeacherChecker($name, $query, $value, $previosValue, $view1, $view2) {
global $conn;
$str = "<div class=\"content-selector\">";
$str .= "<select name=\"".$name."\" onchange=\"submit()\">";
			$str .= "<option></option>";
					$select_result=mysqli_query($conn, $query);
					while($select_row = mysqli_fetch_array($select_result))
						{
							if(stripslashes($select_row[$value])==$previosValue)
								{
									$str .= "<option selected value=\"";
								}
							else
								{
									$str .= "<option value=\"";
								}
							$str .= stripslashes($select_row[$value]);
							$str .= "\">";
							$str .= stripslashes($select_row[$view1])." - ".(($view2 == "stavka") ? "на " : "").
											stripslashes($select_row[$view2]).(($view2 == "stavka") ? " ст." : "");
							$str .= "</option>";
						}
$str .= "</select></div>";
return $str;
}

function paramChekerWithoutWaitAutoSub($first, $second, $third)
{
	$str = "<div class=\"content-checkbox\">
		<input type=\"checkbox\" name=\"".$first."\" onchange=\"submit()\" ";
			if($second)
				{
					$str .= " checked=\"checked\" />".$third."<br>";
				}
			else
				{
					$str .= "/>".$third."<br>";
				}
	$str .= "</div>";
	return $str;
}

function paramChekerAutoSub($first, $second, $third)
{
	$str = "<div class=\"content-checkbox\">
		<input type=\"checkbox\" name=\"".$first."\" 
		onchange=\"document.getElementById('wait').style.display = 'inline'; submit();\" ";
			if($second)
				{
					$str .= " checked=\"checked\" />".$third."<br>";
				}
			else
				{
					$str .= "/>".$third."<br>";
				}
	$str .= "</div>";
	return $str;
}

function paramChekerAutoSubInline($first, $second, $third)
{
	$str = "<input type=\"checkbox\" name=\"".$first."\" 
		    onchange=\"document.getElementById('blink').hidden = false; submit();\" ";
			if($second)
				{
					$str .= " checked=\"checked\" />".$third;
				}
			else
				{
					$str .= "/>".$third;
				}
	return $str;
}

function weeks($weekCount, $weekCompare) {
	for ($i = 1; $i <= $weekCount; $i++) {
		if ($i == $weekCompare) {
			echo("<option selected value=\"");
		} else {
			echo("<option value=\"");
		}
		echo $i;echo("\">");echo $i;
		echo("</option>");
	}
}
function selectWeekChecker($name, $weekCount, $previosValue)
{
	echo("<div class=\"content-selector\">");
		echo("<select name=\"".$name."\">");
		echo("<option></option>");
			weeks($weekCount, $previosValue);
		echo("</select>
	</div>");
}
function isGoodMark($mark) {
	if(empty($mark))
		return "-";
	else
		return $mark;
}
function markChecker($current, $maximum) {
	if(empty($current)) {
		return "-";
	}
	$mark=$current/$maximum;
	if($mark>=0.9) {
		return "5";
	}
	if($mark>=0.75) {
		return "4";
	}
	if($mark>=0.6) {
		return "3";
	}
	return "2";
}
?>
