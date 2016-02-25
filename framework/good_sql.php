<?php
// documentation :: 
// http://www.anyexample.com/programming/php/5_useful_php_functions_for_mysql_data_fetching.xml

function good_query($string, $debug=0) {

$string = str_replace("\t"," ",$string);
$string = str_replace("    "," ",$string);
$string = str_replace("   "," ",$string);
$string = str_replace("  "," ",$string);
$string = str_replace("  "," ",$string);
$string = trim($string);

    if ($debug == 1)
        print $string;

    if ($debug == 2)
        error_log($string);

    $result = mysql_query($string);

    if ($result == false)
    {
        error_log("SQL error: ".mysql_error()."\n\nOriginal query: $string\n");
        // Remove following line from production servers 
        die("SQL error: ".mysql_error()."\b<br>\n<br>Original query: $string \n<br>\n<br>");
    }
    return $result;
}

function good_query_list($sql, $debug=0)
{
    // this function require presence of good_query() function
    $result = good_query($sql, $debug);
    
    if($lst = mysql_fetch_row($result))
    {
        mysql_free_result($result);
        return $lst;
    }
    mysql_free_result($result);
    return false;
}

function good_query_assoc($sql, $debug=0)
{
    // this function require presence of good_query() function
    $result = good_query($sql, $debug);
    
    if($lst = mysql_fetch_assoc($result))
    {
        mysql_free_result($result);
        return $lst;
    }
    mysql_free_result($result);
    return false;
}

function good_query_value($sql, $debug=0)
{
    // this function require presence of good_query_list() function
    $lst = good_query_list($sql, $debug);
    return is_array($lst)?$lst[0]:false;
}

function good_query_table($sql, $debug=0)
{
    // this function require presence of good_query() function
    $result = good_query($sql, $debug);
    
    $table = array();
    if (mysql_num_rows($result) > 0)
    {
        $i = 0;
        while($table[$i] = mysql_fetch_assoc($result)) 
            $i++;
        unset($table[$i]);                                                                                  
    }                                                                                                                                     
    mysql_free_result($result);
    return $table;
}

function good_query_verticallist($sql) {
	$big = good_query_table($sql);
	$skinny = array();
	foreach ($big as $row) {
		foreach ($row as $k => $v) {
			$skinny[] = $v;
		}
	}
	return $skinny;
}

?>