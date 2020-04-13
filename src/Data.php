<?php
namespace phpcrud;
$obj_auth=new \phplibrary\Auth();

$obj_auth->fn_check_ajax();

use PhpMyAdmin\SqlParser\{Parser,Context};
use PhpMyAdmin\SqlParser\Components\{Expression,OptionsArray, Condition, Limit};
use PhpMyAdmin\SqlParser\Statements\{CreateStatement, SelectStatement};
use PhpMyAdmin\SqlParser\Tests\Components\ConditionTest;
use PhpMyAdmin\SqlParser\Utils\{Query};

class Data {
  use \phplibrary\General;
  function __construct() {

    $this->bln_debug=false;
    if($this->bln_debug){$this->fn_echo(">>>>>>>>>>>>>>>>>>>>START __construct");}

    $this->obj_theme=new ThemeData();
    $this->obj_request=new \phplibrary\ServerVariables();
    $this->fn_initialize_conn();

    $this->fn_initialize_var();

    $this->fn_initialize_post();
    $this->fn_initialize_form();//get all submitted form values
    $this->fn_initialize_sql();
    $this->fn_initialize_navigation();

    if($this->bln_debug){$this->fn_echo(">>>>>>>>>>>>>>>>>>>>END __construct");}
  }
  function fn_initialize_var(){

    //define any values of variables without issue here.
    $this->fn_initialize_id();

    $this->fn_load_session_navigation();


    $this->int_num_row_per_page_default="5";
    $this->bln_display_record_console=true;
    $this->bln_disable_nav_start=false;
    $this->bln_disable_nav_end=false;
    $this->str_data_message="";

    $this->bln_count_rs=true;
    $this->bln_ignore_limit=false;
    $this->bln_ignore_key_filter=false;
    if(!empty($this->str_sql)){
      $this->str_sql_external=$this->str_sql;
    }
    else{
      $this->str_sql="";
    }
    $this->str_sql_no_limit="";
    $this->bln_new=false;
    $this->bln_disable_new=false;
    $this->bln_record_console=true;
    $this->fn_ini_action_children(false);
  }
  function fn_ini_action_children($bln_val){
    $this->str_id_record_parent="cid";
    $this->str_id_record_child="id";

    $this->bln_record_copy_children=$bln_val;
    $this->bln_record_view_children=$bln_val;
    $this->bln_record_delete_all_children=$bln_val;
  }
  function fn_initialize_post(){
    if($this->bln_debug){$this->fn_echo("fn_initialize_post");}
    $this->bln_debug_post=$this->bln_debug;
    //$this->bln_debug_post=true;
    $obj_request=$this->obj_request;

    $this->str_action=$obj_request->fn_get_post_value("action");
    $this->str_formvar=$obj_request->fn_get_post_value("formvar");
    $this->str_nav_cmd=$obj_request->fn_get_post_value("nav_cmd");//offset number
    if($this->bln_debug_post){$this->fn_echo("str_action", $this->str_action);}
    if($this->bln_debug_post){$this->fn_echo("str_formvar", $this->str_formvar);}
    if($this->bln_debug_post){$this->fn_echo("str_nav_cmd", $this->str_nav_cmd);}

    $str_id_table_record=$obj_request->fn_get_post_value("str_id_table_record");
    $str_id_record=$obj_request->fn_get_post_value("str_id_record");
    $int_id_record=$obj_request->fn_get_post_value("int_id_record");//this is generated from the dynamic query select / button interaction
    $this->fn_initialize_id($str_id_table_record, $str_id_record, $int_id_record);
    if($this->bln_debug_post){$this->fn_debug_id("POST VALUES");}

    $int_num_row_per_page=$obj_request->fn_get_post_value("row_per_page");
    if(empty($int_num_row_per_page)){
      $int_num_row_per_page=$this->int_num_row_per_page_default;
    }
    $this->int_num_row_per_page=$int_num_row_per_page;

    $str=$obj_request->fn_get_post_value("str_data_query");
    $this->str_data_query=trim($str);

    if(empty($this->str_data_schema)){
      $this->str_data_schema=$obj_request->fn_get_post_value("str_data_schema");
    }
    if(empty($this->str_data_schema)){
      $this->str_data_schema=$this->con_schema;
    }
    if(empty($this->str_data_table)){
      $this->str_data_table=$obj_request->fn_get_post_value("str_data_table");
    }
  }

  function fn_initialize_id($str_id_table_record="", $str_id_record="", $int_id_record=0){
    //Does this need to be revised, so as not to reset the $str_id_table_record $str_id_record to blank ?
    //certianly the int_id_record does need to be blanked.

    $this->str_id_table_record=$str_id_table_record;
    $this->str_id_record=$str_id_record;
    $this->int_id_record=$int_id_record;
    if($this->bln_debug){$this->fn_debug_id("fn_initialize_id");}
  }
  function fn_debug_id($str_message=""){

    if(!empty($str_message)){$this->fn_echo($str_message);}
    $this->fn_echo("str_id_table_record", $this->str_id_table_record);
    $this->fn_echo("str_id_record", $this->str_id_record);
    $this->fn_echo("int_id_record", $this->int_id_record);
  }

  function fn_initialize_conn(){
    if($this->bln_debug){$this->fn_echo("fn_initialize_conn");}
    $obj_request=$this->obj_request;

    if(empty($this->con_host)){
      $this->con_host=$obj_request->fn_get_post_value("str_con_host");
    }
    if(empty($this->con_user)){
      $this->con_user=$obj_request->fn_get_post_value("str_con_user");
    }
    if(empty($this->con_pass)){
      $this->con_pass=$obj_request->fn_get_post_value("str_con_pass");
    }
    if(empty($this->con_schema)){
      $this->con_schema=$obj_request->fn_get_post_value("str_con_schema");
    }

    if(empty($this->con_host)){
      die("error: no connection host");
    }
    if(empty($this->con_user)){
      die("error: no connection user");
    }
    if(empty($this->con_pass)){
      die("error: no connection pass");
    }
    if(empty($this->con_schema)){
      //die("error: no connection default schema");
    }
    if(empty($this->str_data_schema)){
      //die("error: no native data table");
    }
    if(empty($this->str_data_table)){
      //die("error: no native data table");
    }
    $this->obj_pdo=new \phplibrary\PDO();
    $this->obj_pdo->fn_connect($this->con_host,$this->con_user,$this->con_pass,$this->con_schema);
  }

  function fn_initialize_form() { //get any form variables that ahve been sent up
    if($this->bln_debug){$this->fn_echo("fn_initialize_form");}

    $this->str_search_expr="";

    $this->arr_form=[];
    parse_str ($this->str_formvar, $this->arr_form);
    if($this->bln_debug){$this->fn_dump($this->arr_form);}
    //$this->fn_dump($this->arr_form);

    $this->str_search="";
    if(isset($this->arr_form["data-srh"])){//data-srh is linked to client form
      $str_val=$this->arr_form["data-srh"];//data-input-search is linked to client form
      if(!empty($str_val)){
        $str_val="%".trim($str_val)."%";
      }
      $this->str_search=$str_val;
    }
    if (!isset($_SESSION["str_search"])){
      $_SESSION["str_search"]="";
    }
    /*
    $this->fn_echo("SESSION[str_search]", $_SESSION["str_search"]);
    $this->fn_echo("this->str_search", $this->str_search);
    //*/
    if ($_SESSION["str_search"]!==$this->str_search){
      $this->fn_reset_navigation();
    }
    $_SESSION["str_search"]=$this->str_search;
  }

  function fn_execute(){
    if($this->bln_debug){$this->fn_echo("fn_execute");}
    $this->fn_action_rs();
  }
  function fn_action_rs(){
    if($this->bln_debug){
      $this->fn_echo("fn_action_rs");
      $this->fn_echo("this->str_action", $this->str_action);
      $this->fn_echo("this->str_search", $this->str_search);
    }
    switch ($this->str_action) {
      case "get-list-schema":
        $this->fn_get_list_schema();
      break;
      case "get-list-table":
        $this->fn_get_list_table();
      break;
      case "record-view":
        $this->fn_record_view();
      break;
      case "records-new":
        $this->fn_records_new();
      break;
      case "record-new":
        $this->fn_record_new();
      break;
      case "records-view":
        $this->fn_records_view();
      break;
      case "records-cancel":
        $this->fn_records_cancel();
      break;
      case "record-cancel":
        $this->fn_record_cancel();
      break;
      case "record-edit":
        $this->bln_edit=true;
        $this->fn_record_edit();
      break;
      case "record-save":
        $this->fn_record_save();
      break;
      case "record-copy":
        $this->fn_record_copy();
      break;
      case "record-delete":
        $this->fn_record_delete();
      break;
      case "run-script":
        $this->fn_run_script();
      break;
      default:
      break;
    }
  }
  function fn_execute_query(){
    if($this->bln_debug){$this->fn_echo("fn_execute_query");}
    $this->fn_load_query();
    $this->fn_open_rs();
  }
  function fn_load_query(){
    if($this->bln_debug){$this->fn_echo("start class fn_load_query");}
    $this->fn_initialize_sql();
    $this->fn_initialize_navigation();
    $this->fn_open_query();
    if($this->bln_debug){$this->fn_echo("end class fn_load_query");}
  }
  function fn_initialize_sql(){
    if($this->bln_debug){$this->fn_echo("fn_initialize_sql");}
    $this->arr_where=[];
    $this->arr_search=[];
    $this->params=[];
    $this->str_where="";
    $this->bln_count_rs=true;
  }
  function fn_initialize_navigation(){
    if($this->bln_debug){$this->fn_echo("fn_initialize_navigation");}
    $this->bln_edit=false;
  }
  function fn_load_session_navigation(){
    if($this->bln_debug){$this->fn_echo("fn_load_session_navigation");}
    $int_row_start=0;
    if (isset($_SESSION["int_row_start"])){
        $int_row_start=$_SESSION["int_row_start"];
    }
    $this->int_row_start=$int_row_start;

    $int_row_count=0;
    if (isset($_SESSION["int_row_count"])){
        $int_row_count=$_SESSION["int_row_count"];
    }
    $this->int_row_count=$int_row_count;
  }
  function fn_reset_navigation(){
    if($this->bln_debug){$this->fn_echo("fn_reset_navigation");}
    //$this->fn_echo("fn_reset_navigation");
    //e.g after a record is deleted.
    //e.g after a record is inserted.
    $this->fn_initialize_id();
    $this->fn_initialize_navigation();
    $_SESSION["int_row_start"]=0;
    $_SESSION["int_row_count"]=0;
    $this->fn_load_session_navigation();
  }
  function fn_debug_navigation($str_message=""){
    if(!empty($str_message)){$this->fn_echo($str_message);}
    $this->fn_echo("this->str_nav_cmd", $this->str_nav_cmd);
    $this->fn_echo("this->bln_edit", $this->bln_edit);
    $this->fn_echo("this->bln_display_record_console", $this->bln_display_record_console);
    $this->fn_echo("this->int_num_row_per_page_default", strval($this->int_num_row_per_page_default));
    $this->fn_echo("this->int_row_start", strval($this->int_row_start));
    $this->fn_echo("this->int_num_row_per_page", strval($this->int_num_row_per_page));
    $this->fn_echo("this->int_row_count", strval($this->int_row_count));
    $this->fn_echo("this->bln_disable_nav_start", $this->bln_disable_nav_start);
    $this->fn_echo("this->bln_disable_nav_end", $this->bln_disable_nav_end);
    $this->fn_echo("this->str_data_message", $this->str_data_message);
  }
  function fn_open_query(){
    if($this->bln_debug){$this->fn_echo(">>>>>>>>>>START fn_open_query");}
    if($this->bln_debug){$this->fn_echo("this->str_sql", $this->str_sql);}
    $bln_debug_parser=$this->bln_debug;
    //$bln_debug_parser=true;

    if(!empty($this->str_sql)){}//provided externally or internally
    else{
      if(!empty($this->str_data_query)){
        $this->bln_count_rs=true;
        if($this->bln_debug){$this->fn_echo("use str_data_query");}
        $this->str_sql=$this->str_data_query;
      }
    }
    $this->str_sql = $this->fn_replace_newline($this->str_sql);
    if(empty($this->str_sql)){
      $this->fn_write_container("Query is empty...");
      die();
    }
    $this->fn_define_query();
    //$this->fn_define_column_list();

    if($bln_debug_parser){$this->fn_debug_parser();}

    $str_sql=$this->fn_itrim_from($this->str_sql, ";");
    $str_sql=$this->fn_itrim_from($str_sql, " LIMIT ");
    $str_sql=$this->fn_itrim_from($str_sql, " ORDER BY ");
    $str_sql=$this->fn_itrim_from($str_sql, " WHERE ");
    $str_sql=$this->fn_trim_add_space($str_sql);

    $str_sql=$this->fn_replace_first("SELECT", "SELECT SQL_CALC_FOUND_ROWS ", $str_sql);

    $this->str_sql=$str_sql;
    $this->str_sql_no_limit=$str_sql;
    //$this->fn_echo("pre where this->str_sql_no_limit", $this->str_sql_no_limit);

    $this->fn_compile_where();
    $this->str_sql.=$this->str_where;
    $this->str_sql=$this->fn_trim_add_space($this->str_sql);

    $this->fn_compile_order();
    $this->str_sql.=$this->str_order;
    $this->str_sql=$this->fn_trim_add_space($this->str_sql);

    $this->str_sql_no_limit=$this->str_sql;
    switch ($this->obj_parser_stmt->querytype) {
      case "SELECT":
        if(!$this->bln_ignore_limit){
          $this->str_sql.="LIMIT ".$this->int_row_start.",".$this->int_num_row_per_page;
        }
      break;
    }
    $this->str_sql.=";";
    //$this->fn_echo("this->str_sql", $this->str_sql);

    if($this->bln_debug){$this->fn_echo("this->str_sql", $this->str_sql);}

    $this->fn_define_query();

    if($bln_debug_parser){$this->fn_debug_parser();}
    if($this->bln_debug){$this->fn_echo(">>>>>>>>>>END fn_open_query");}
  }
  function fn_add_where($str_where){
    array_push($this->arr_where, $str_where);
  }
  function fn_compile_where(){

    if($this->bln_debug){$this->fn_echo("fn_compile_where");}

    $str_where_interface="";
    $s="";
    if(!empty($this->str_search)){
      $this->fn_compile_search();
      $s.=$this->fn_add_and($s, $this->str_search_expr);
    }
    if(!empty($this->int_id_record)){//add where id
      $this->fn_compile_where_id();
      $s.=$this->fn_add_and($s, $this->str_where_id);
      //$s.=$this->str_where_id;
    }
    $str_where_interface=$s;

    $s="";
    if(!empty($this->obj_parser_stmt->str_where)){
      $this->fn_add_where("(".$this->obj_parser_stmt->str_where.")");
    }
    if(!empty($str_where_interface)){
      $this->fn_add_where("(".$str_where_interface.")");
    }
    $str_where_expr=implode(" AND ",$this->arr_where);

    $s="";
    if(!empty($str_where_expr)){
      $s.="WHERE ".$str_where_expr;
    }
    $this->str_where=$s;
  }

  function fn_compile_where_id(){
    $s="";
    if(!empty($this->int_id_record and !empty($this->str_id_table_record) and !empty($this->str_id_record))){
      $s.="`".$this->str_id_table_record."`.`".$this->str_id_record."`=".$this->int_id_record." ";//can be searching a join so add a schema
    }
    $this->str_where_id=$s;
  }

  function fn_compile_order(){
    if($this->bln_debug){$this->fn_echo("fn_compile_order");}

    $this->str_order="";
    $s="";
    if(!empty($this->obj_parser_stmt->str_order)){
      $s="ORDER BY ".$this->obj_parser_stmt->str_order;
    }
    $this->str_order=$s;
  }
  function fn_add_search($str_search){
    array_push($this->arr_search, $str_search);
  }
  function fn_add_param($foo_value){
    array_push($this->params, $foo_value);
  }
  function fn_compile_search(){

    $this->str_search_expr="";

    if(!empty($this->str_search)){
      $arr_expr=$this->obj_parser_stmt->expr;
      $str_expr=implode($arr_expr);
      if($str_expr==="*"){
          $this->fn_define_column_list();
          $arr_expr=$this->obj_parser_stmt->arr_column_list;
      }
      foreach ($arr_expr as $key=>$value) {//compile where string
        $this->fn_add_search($value." LIKE ?");
        $this->fn_add_param($this->str_search);
      }
    }

    $s="";
    if(count($this->arr_search)){

      $arr=$this->arr_search;
      $s.="(";
      foreach ($arr as $key=>$value) {//compile where string
        $s.=$value." OR ";
      }
      $s = rtrim($s, " OR ");
      $s.=") ";
    }
    $this->str_search_expr=$s;
  }

  function fn_open_rs(){
    if($this->bln_debug){$this->fn_echo("&nbsp;");$this->fn_echo(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>START fn_open_rs");}

    try{
      if($this->bln_debug){$this->fn_echo("final prior prepare sql ", $this->str_sql);}
      //$this->fn_echo("final prior prepare sql ", $this->str_sql);
      $this->stmt=$this->obj_pdo->pdo->prepare($this->str_sql);
    }
    catch(\PDOException $e) {
        $this->fn_write_message("Error", $e->getMessage());
        die();
    }
    //echo $this->obj_pdo->interpolateQuery($this->str_sql, $this->params);
    if($this->bln_debug){$this->fn_write_array($this->params);}

    try{
      $this->stmt->execute($this->params);
    }
    catch(\PDOException $e) {
        $this->fn_write_message("Error", $e->getMessage());
        die();
    }

    $this->bln_disable_new=$this->fn_in_istr("join", $this->str_sql);
    if(!$this->bln_disable_new){
      if(empty($this->obj_parser_stmt->str_data_table)){
        $this->bln_disable_new=true;
      }
    }

    $this->int_row_count=1;

    if($this->bln_count_rs){
      $str=strtolower($this->str_sql);
      $bln_val=$this->fn_starts_with($str, "select");
      if($bln_val){
          $this->fn_count_rs();
      }
    }
    $this->str_sql="";//we reset here because it could be an insert etc
    if(!empty($this->str_sql_external)){
      $this->str_sql=$this->str_sql_external;
    }
    if($this->bln_debug){$this->fn_echo(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>END fn_open_rs");$this->fn_echo("&nbsp;");}
  }
  function fn_count_rs(){
    if($this->bln_debug){$this->fn_echo("fn_count_rs");}

    $stmt = $this->obj_pdo->pdo->query("SELECT FOUND_ROWS()");
    $row = $stmt->fetch(\PDO::FETCH_NUM);
    $this->int_row_count=$row[0];
    if($this->int_row_start>$this->int_row_count){
      $this->int_row_count=0;
    }
    unset($stmt);
    unset($row);
    if($this->bln_debug){$this->fn_echo("this->int_row_count", $this->int_row_count);}
  }
  function fn_rowCount($stmt=""){
    if($this->bln_debug){$this->fn_echo("fn_count_rs");}

    if(empty($stmt)){
      $stmt=$this->stmt;
    }
    $int_count_affected = $stmt->rowCount();
    if($this->bln_debug){$this->fn_echo("this->$int_count_affected", $this->int_count_affected);}
  }
  function fn_close_rs(){
  }
  //CARD
  function fn_write_card($row=false){


        $s="";
    $int_column_count=$this->stmt->columnCount();
    //$this->fn_echo("int_column_count", $int_column_count);

    $this->str_form_id_current_record='data-form-'.$this->int_form_counter++;//unqiue dom id used to identify and serialize the form data


    $str_form_action="record-save";
    if($this->bln_new){
      $str_form_action="record-new";
    }

    echo '<div id="data-card" style="overflow-x:auto" class="d-flex w-50 p-3 my-3 '.$this->obj_theme->str_class.'">'.PHP_EOL;
    echo '<form frm-record frm-action="'.$str_form_action.'" class="data-form-class" id="'.$this->str_form_id_current_record.'">'.PHP_EOL;
    echo PHP_EOL;

    $bln_run_form=true;
    if(!$this->int_row_count){
      if(!$this->bln_new){
        echo "Nothing to see here...".PHP_EOL;
        $bln_run_form=false;
      }
    }
    if($bln_run_form){

      $this->bln_found_key=false;
      $this->bln_has_editable=false;
      for ($i = 0; $i < $int_column_count; $i++) {
        $arr_col = $this->stmt->getColumnMeta($i);
        //$this->fn_dump($arr_col);
        $this->str_name=$arr_col['name'];
        $this->str_table=$arr_col['table'];
        $this->bln_field_editable=$this->bln_edit;
        if(empty($this->str_table)){
          $this->bln_field_editable=false;
        }
        else{
          $this->bln_has_editable=true;
        }
        $this->str_value="";
        if(isset($row[$i])){
          $this->str_value=htmlspecialchars($row[$i]);
        }
        $this->arr_flags=$arr_col['flags'];
        //$this->fn_dump($this->arr_flags);
        $bln_key=in_array("primary_key", $this->arr_flags);

        if($bln_key and !$this->bln_found_key){
          $this->bln_found_key=true;
          //$this->fn_dump($arr_col);
          if(!$this->bln_new){
            $this->int_id_record=$this->str_value;
          }
          $this->str_id_record=$this->str_name;
          $this->str_id_table_record=$this->str_table;
          /*
          $this->fn_echo("this->int_id_record", $this->int_id_record);
          $this->fn_echo("this->str_id_record", $this->str_id_record);
          $this->fn_echo("this->str_id_table_record", $this->str_id_table_record);
          //*/
        }
        $this->str_type=$arr_col['pdo_type'];
        //$this->fn_echo("this->str_type", $this->str_type);
        //$this->fn_echo("this->str_native_type", $this->str_native_type);
        if($this->bln_new){
          $this->str_value="";
        }
        $this->str_type_html="text";
        $this->fn_write_row();
      }//End for loop
    }

    if($this->bln_edit){
      echo '<input type="submit" ';
      echo 'record-id-table="'.$this->str_id_table_record.'" ';
      echo 'record-id-name="'.$this->str_id_record.'" ';
      echo 'record-id="'.$this->int_id_record.'" ';
      echo 'style="display:block" ';
      echo 'name="frm-submit" ';
      echo 'id="'.$this->str_form_id_current_record.'-submit" ';
      echo 'value="'.$this->int_id_record.'" ';
      echo '>'.PHP_EOL;
    }

    echo '</form>'.PHP_EOL;
    echo '</div>'.PHP_EOL;

    if($this->int_row_count){
      if($this->bln_record_console){
        $this->fn_display_button();
      }
    }
    echo PHP_EOL;
  }
  function fn_write_row(){

    $s="";
    $s.='<div class="form-group row">'.PHP_EOL;
    $s.='<div class="col" for="'.$this->str_name.'">'.PHP_EOL;
    $s.=$this->str_name.':&nbsp;'.PHP_EOL;
    $s.='</div>'.PHP_EOL;
    $s.='<div class="col" style=" word-break: xbreak-word;">'.PHP_EOL;
    if($this->bln_field_editable){
      $s.='<input type="text" name="'.$this->str_name.'" value="'.$this->str_value.'" placeholder="" class="form-control input-lg" >'.PHP_EOL;
    }
    else{
      $s.=$this->str_value.PHP_EOL;
    }
    $s.='</div>'.PHP_EOL;
    $s.='</div>'.PHP_EOL;
    $s.=PHP_EOL;
    echo $s;
  }
  function fn_display_button(){

    $str_disabled="";
    if($this->int_id_record==""){
      if(!$this->bln_new){
        $str_disabled="disabled";
        $this->bln_disable_new=true;
      }
    }

    if(!$this->bln_has_editable){
        $str_disabled="disabled";
        $this->bln_disable_new=true;
    }


    if(!$this->bln_display_record_console and !$this->bln_edit){
        $this->fn_write_button("record-view", "View", $str_disabled);
        return;
    }

    switch ($this->bln_edit) {
      case true:
        if($this->bln_new){
          $this->fn_write_button("record-new", "Save", $str_disabled);
        }
        else{
          $this->fn_write_button("record-save", "Save", $str_disabled);
        }

        $this->fn_write_button("record-cancel", "Cancel", $str_disabled);
      break;
      case false:
        $this->fn_write_button("record-edit", "Edit", $str_disabled);
        $this->fn_write_button("record-copy", "Copy", $str_disabled);
        $this->fn_write_button("record-delete", "Delete", $str_disabled);
        if($this->str_action!="records-view"){
          $this->fn_write_button("records-cancel", "Cancel");
        }
      break;
    }
  }
  //CARD

  function fn_write_button($str_function, $str_text, $str_disabled=""){
    $obj_theme=$this->obj_theme;


    echo '<button ';
    echo 'type="button" ';
    echo 'record-btn ';
    echo 'record-id-table="'.$this->str_id_table_record.'" ';
    echo 'record-id-name="'.$this->str_id_record.'" ';
    echo 'record-id="'.$this->int_id_record.'" ';
    echo 'form-id="'.$this->str_form_id_current_record.'" ';//unqiue dom id used to identify and serialize the form data
    echo 'frm-action="'.$str_function.'" ';
    echo 'class="btn btn-'.$obj_theme->str_theme_color.'" ';
    echo $str_disabled;
    echo '>';
    echo $str_text;
    echo '</button>';
    echo PHP_EOL;
  }
  //RECORDS
  function fn_records_view($bln_ignore_limit=false){
    if($this->bln_debug){$this->fn_echo("fn_records_view");}

    $this->bln_ignore_limit=$bln_ignore_limit;

    $this->fn_initialize_id();
    $this->fn_locate_records();
    $this->fn_execute_query();
    $this->fn_records_write();
  }
  function fn_locate_records() {
    if($this->bln_debug){$this->fn_echo("fn_locate_records");}

    $int_row_start=$this->int_row_start;
    $int_num_row_per_page=$this->int_num_row_per_page;
    $int_row_count=$this->int_row_count;

    switch ($this->str_nav_cmd){
      case "nav-num-row":
      break;
      case "nav-reset":
        $this->int_row_count=0;
        $int_row_start=0;
      break;
      case "nav-srt":
        $int_row_start=0;
      break;
      case "nav-bck":
        $int_row_start-=$int_num_row_per_page;
      break;
      case "nav-fwd":
        $int_row_start+=$int_num_row_per_page;
      break;
      case "nav-end":
        $calcmod=$int_row_count%$int_num_row_per_page;
        if(!$calcmod){$calcmod=$int_num_row_per_page;}
        $int_row_start=$int_row_count-$calcmod;
      break;
      default:
      break;
    }
    $this->str_nav_cmd="";
    if($int_row_start<0){$int_row_start=0;}
    $this->int_row_start=$int_row_start;
  }
  function fn_records_write(){
    if($this->bln_debug){$this->fn_echo("fn_records_write");}

    $this->int_form_counter=0;
    if(!$this->int_row_count){
      $this->fn_write_card(false);
    }
    else{
      while($row=$this->stmt->fetch(\PDO::FETCH_BOTH)){
        $this->fn_write_card($row);
      }
    }
    $this->fn_write_data_information();
  }
  function fn_records_cancel(){
    if($this->bln_debug){$this->fn_echo("fn_records_cancel");}
    $this->str_action="records-view";
    $this->fn_action_rs();
  }
  //RECORDS

  //RECORD
  function fn_locate_record(){
    if($this->bln_debug){$this->fn_echo("fn_locate_record");}
    $this->int_row_start=0;
  }

  function fn_record_view(){
    if($this->bln_debug){$this->fn_echo("fn_record_view");}

    $this->fn_action_record_view();

    if($this->bln_record_view_children){
      $this->fn_record_view_children($this->int_id_record);
    }
  }
  function fn_action_record_view(){
    $this->fn_locate_record();
    $this->fn_execute_query();
    $this->bln_display_record_console=true;
    $this->fn_record_write();
  }

  function fn_record_view_children($int_id_record_parent){

    $str_data_schema=$this->obj_parser_stmt->str_data_schema;
    $str_data_table=$this->obj_parser_stmt->str_data_table;
    $this->bln_display_record_console=false;
    $this->str_sql="SELECT * FROM `".$str_data_schema."`.`".$str_data_table."` WHERE `$this->str_id_record_parent`=$int_id_record_parent;";
    $this->str_action="records-view";
    $this->fn_action_rs();
  }

  function fn_record_view_all_children($int_id_record_parent){

    $str_data_schema=$this->obj_parser_stmt->str_data_schema;
    $str_data_table=$this->obj_parser_stmt->str_data_table;
    $str_sql="SELECT * FROM `".$str_data_schema."`.`".$str_data_table."` WHERE `$this->str_id_record_parent`=$int_id_record_parent;";
    $stmt=$this->obj_pdo->pdo->query($str_sql);
    while($row=$stmt->fetch(\PDO::FETCH_ASSOC)){
      $this->int_id_record=$row["$this->str_id_record_child"];
      $this->bln_display_record_console=false;
      $this->str_sql="SELECT * FROM `".$str_data_schema."`.`".$str_data_table."` WHERE `$this->str_id_record_child`=$this->int_id_record;";
      $this->fn_action_record_view();
      $this->fn_record_view_all_children($this->int_id_record);
    }
  }

  function fn_record_write(){
    if($this->bln_debug){$this->fn_echo("fn_record_write");}

    if($this->bln_debug){$this->fn_debug_id("SINGLE RECORD VIEW VALUES");}
    //echo '<h2>Form</h2>';

    $row=$this->stmt->fetch(\PDO::FETCH_BOTH);
    $this->int_form_counter=0;
    $this->fn_write_card($row);
    $this->fn_write_data_information();
  }
  //PAGE
  function fn_write_data_information(){
    if($this->bln_debug){$this->fn_echo("fn_write_data_information");}

    if($this->bln_new){
      $this->bln_disable_new=true;
    }

    //WRITE VARIABLES BACK TO SESSION
    $_SESSION["int_row_count"]=$this->int_row_count;
    $_SESSION["int_row_start"]=$this->int_row_start;
    //WRITE VARIABLES BACK TO SESSION

    $int_row_start=$this->int_row_start;
    $int_num_row_per_page=$this->int_num_row_per_page;
    $int_row_count=$this->int_row_count;

    //Set Page Varaibles
    $int_max_extent=$int_row_count-$int_num_row_per_page;
    $int_row_end=$int_row_start+$int_num_row_per_page;
    if($int_row_end>$int_row_count){$int_row_end=$int_row_count;}
    //Set Page Varaibles

    //Disable Selected Controls
    $bln_disable_nav_start=false;
    if(!$int_row_count){$bln_disable_nav_start=true;}
    if(!$this->int_num_row_per_page){$bln_disable_nav_start=true;}
    if(!$int_row_start){$bln_disable_nav_start=true;}
    $this->bln_disable_nav_start=$bln_disable_nav_start;

    $bln_disable_nav_end=false;
    if(!$int_row_count){$bln_disable_nav_end=true;}
    if(!$this->int_num_row_per_page){$bln_disable_nav_end=true;}
    if($int_row_start>=$int_max_extent){$bln_disable_nav_end=true;}
    $this->bln_disable_nav_end=$bln_disable_nav_end;
    //Disable Selected Controls

    //Set Records Message
    $int_row_start=$int_row_start+1;
    $str_message="Records ".$int_row_start." to ".$int_row_end." of ".$int_row_count;
    if($int_row_start==$int_row_end){
      $str_message="Record ".$int_row_start." of ".$int_row_count;
    }
    if(!$int_row_count){$str_message="";}
    $this->str_data_message=$str_message;
    //Set Records Message

    echo '<input type="hidden" data-action value="'.$this->str_action.'">'.PHP_EOL;
    echo '<input type="hidden" data-message value="'.$this->str_data_message.'">'.PHP_EOL;
    echo '<input type="hidden" data-disable-nav-start value="'.$this->bln_disable_nav_start.'">'.PHP_EOL;
    echo '<input type="hidden" data-disable-nav-end value="'.$this->bln_disable_nav_end.'">'.PHP_EOL;
    echo '<input type="hidden" data-disable-new value="'.$this->bln_disable_new.'">'.PHP_EOL;
    $this->fn_write_connection_information();
  }
  function fn_write_connection_information($tag="data"){
    echo '<input type="hidden" '.$tag.'-count-process value="'.$this->obj_pdo->int_count_process.'">'.PHP_EOL;
    echo '<input type="hidden" '.$tag.'-default-schema value="'.$this->obj_pdo->str_default_schema.'">'.PHP_EOL;
  }
  //PAGE

  function fn_records_new(){
    if($this->bln_debug){$this->fn_echo("fn_records_new");}

    $this->fn_locate_record();
    $this->fn_execute_query();
    $this->bln_edit=true;
    $this->bln_new=true;
    $this->bln_display_record_console=true;
    $this->fn_record_write();
  }
  function fn_record_new(){
    if($this->bln_debug){$this->fn_echo("fn_records_new");}

    $this->fn_load_query();
    $this->fn_compile_sql_insert();
    $this->fn_open_rs();

    $this->int_id_record=$this->fn_get_last_insert_id();
    $this->str_action=$this->fn_redirect_record_view();
    $this->fn_action_rs();
  }

  function fn_record_copy(){
    if($this->bln_debug){$this->fn_echo("fn_record_copy");}

    $this->fn_action_record_copy();
    $int_id_record_copy=$this->int_id_record_copy;

    if($this->bln_record_copy_children){
      $this->fn_record_copy_children($this->int_id_record, $this->int_id_record_copy);
    }

    $this->int_id_record=$int_id_record_copy;
    $this->str_action=$this->fn_redirect_record_view();
    $this->fn_action_rs();
  }
  function fn_action_record_copy(){
    $this->fn_load_query();
    $this->fn_compile_sql_copy();
    $this->fn_open_rs();
    $this->int_id_record_copy=$this->fn_get_last_insert_id();
  }

  function fn_record_copy_children($int_id_record_parent, $int_id_record_parent_copy){

    $str_data_schema=$this->obj_parser_stmt->str_data_schema;
    $str_data_table=$this->obj_parser_stmt->str_data_table;
    $str_sql="SELECT * FROM `".$str_data_schema."`.`".$str_data_table."` WHERE `$this->str_id_record_parent`=$int_id_record_parent;";
    $stmt=$this->obj_pdo->pdo->query($str_sql);
    while($row=$stmt->fetch(\PDO::FETCH_ASSOC)){

      $this->int_id_record=$row["$this->str_id_record_child"];
      $this->str_sql="SELECT * FROM `".$str_data_schema."`.`".$str_data_table."` WHERE `$this->str_id_record_child`=$this->int_id_record;";
      $this->fn_action_record_copy();

      $str_sql="UPDATE `".$str_data_schema."`.`".$str_data_table."` SET `$this->str_id_record_parent`=$int_id_record_parent_copy WHERE `$this->str_id_record_child`=$this->int_id_record_copy;";
      $this->obj_pdo->pdo->query($str_sql);

      $this->fn_record_copy_children($this->int_id_record, $this->int_id_record_copy);
    }
  }


  function fn_redirect_record_view(){
    if(empty($this->int_id_record) or empty($this->str_id_record) or empty($this->str_id_table_record)){//May not be set if no key was on the form
        return "records-view";
    }
    return "record-view";
  }
  function fn_record_edit(){
    if($this->bln_debug){$this->fn_echo("fn_record_edit");}

    $this->fn_locate_record();
    $this->fn_execute_query();
    $this->bln_edit=true;
    $this->bln_display_record_console=true;
    $this->fn_record_write();
  }
  function fn_record_save(){
    if($this->bln_debug){$this->fn_echo("fn_record_save");}

    $this->fn_locate_record();
    $this->fn_load_query();
    $this->fn_compile_sql_update();
    $this->fn_open_rs();

    $this->str_action="record-view";
    $this->fn_action_rs();
  }
  function fn_record_delete(){

    if($this->bln_debug){$this->fn_echo("fn_record_delete");}

    $int_id_record=$this->int_id_record;
    $this->fn_action_record_delete();

    if($this->bln_record_delete_all_children){
      $this->fn_record_delete_all_children($int_id_record);
    }

    $this->fn_reset_navigation();

    $this->str_action="records-view";
    $this->fn_action_rs();
  }
  function fn_action_record_delete(){
    $this->fn_locate_record();
    $this->fn_load_query();
    $this->fn_compile_sql_delete();
    $this->fn_open_rs();
  }

  function fn_record_delete_children($int_id_record_parent){

    if(empty($int_id_record_parent)){
      //Saftey, should never see this
      $this->fn_write_message("Error", "Delete Children: Parent Id is Zero. Aborting");
      return;
    }
    $str_data_schema=$this->obj_parser_stmt->str_data_schema;
    $str_data_table=$this->obj_parser_stmt->str_data_table;
    $this->str_sql="SELECT * FROM `".$str_data_schema."`.`".$str_data_table."` WHERE `$this->str_id_record_parent`=$int_id_record_parent;";
    $this->fn_action_record_delete();
  }

  function fn_record_delete_all_children($int_id_record_parent){

    if(empty($int_id_record_parent)){
      //Saftey, should never see this
      $this->fn_write_message("Error", "Delete Children: Parent Id is Zero. Aborting");
      return;
    }
    $str_data_schema=$this->obj_parser_stmt->str_data_schema;
    $str_data_table=$this->obj_parser_stmt->str_data_table;
    $str_sql="SELECT * FROM `".$str_data_schema."`.`".$str_data_table."` WHERE `$this->str_id_record_parent`=$int_id_record_parent;";
    $stmt=$this->obj_pdo->pdo->query($str_sql);
    while($row=$stmt->fetch(\PDO::FETCH_ASSOC)){
      $this->int_id_record=$row["$this->str_id_record_child"];
      $this->str_sql="SELECT * FROM `".$str_data_schema."`.`".$str_data_table."` WHERE `$this->str_id_record_child`=$this->int_id_record;";
      $this->fn_action_record_delete();
      $this->fn_record_delete_all_children($this->int_id_record);
    }
  }

  function fn_record_cancel(){
    if($this->bln_debug){$this->fn_echo("fn_record_cancel");}
    $this->str_action="record-view";
    $this->fn_action_rs();
  }

  //RECORD

  function fn_get_list_schema(){
    if($this->bln_debug){$this->fn_echo("fn_get_list_schema");}
    $stmt = $this->obj_pdo->pdo->query("SHOW SCHEMAS;");
    $s='';
    $s.='<select id="data-list-schema" name="list-schema" class="form-control mb-1 mr-1">';
    $s.=$this->fn_get_select_option("", "");
    while ($row = $stmt->fetch(\PDO::FETCH_NUM))
    {
      $str_schema=$row[0];
      $s.=$this->fn_get_select_option($str_schema, $this->con_schema);
    }
    $s.='</select>';
    echo $s;
    $this->fn_write_connection_information("schema");
  }
  function fn_get_list_table(){
    if($this->bln_debug){$this->fn_echo("fn_get_list_table");}
    if(empty($this->str_data_schema)){return "";}
    $stmt = $this->obj_pdo->pdo->query("SHOW TABLES FROM `".$this->str_data_schema."`;");
    $s='';
    $s.='<select id="data-list-table" name="list-table" class="form-control mb-1 mr-1">';
    //$s.=fn_get_select_option("", "");
    while ($row = $stmt->fetch(\PDO::FETCH_NUM))
    {
      $str_table=$row[0];
      $s.=$this->fn_get_select_option($str_table, "");
    }
    $s.='</select>';
    echo $s;
    $this->fn_write_connection_information("table");
  }
  function fn_compile_sql_insert(){
    if($this->bln_debug){$this->fn_echo("fn_compile_sql_insert");}

    $arr_action=$this->fn_get_qualified_database_array($this->obj_parser_stmt->str_data_schema, $this->obj_parser_stmt->str_data_table, $this->arr_form);
    $str_seperator=", ";

    $s="";
    $s.="INSERT INTO ";
    $s.="`".$this->obj_parser_stmt->str_data_schema."`.`".$this->obj_parser_stmt->str_data_table."` ";
    $s.="(".PHP_EOL;
    foreach ($arr_action as $key=>$value) {//compile where string
      $s.="`".$key."`".$str_seperator;
    }
    $s = rtrim($s, $str_seperator);
    $s.=") ".PHP_EOL;
    $s.="VALUES ";
    $s.="(".PHP_EOL;
    foreach ($arr_action as $key=>$value) {//compile where string
      $s.=$value.$str_seperator;
    }
    $s = rtrim($s, $str_seperator);
    $s.=") ".PHP_EOL;
    $s.=";".PHP_EOL;
    $this->str_sql=$s;
    //$this->fn_echo("this->str_sql", $this->str_sql);
  }

  function fn_compile_sql_copy(){
    if($this->bln_debug){$this->fn_echo("fn_compile_sql_copy");}

    $arr_action=$this->fn_get_qualified_database_array($this->obj_parser_stmt->str_data_schema, $this->obj_parser_stmt->str_data_table);
    $str_seperator=", ";

    $s="";
    $s.="INSERT INTO ";
    $s.="`".$this->obj_parser_stmt->str_data_schema."`.`".$this->obj_parser_stmt->str_data_table."` ";
    $s.="(";
    foreach ($arr_action as $key=>$value) {//compile where string
      $s.="`".$key."`".$str_seperator;
    }
    $s = rtrim($s, $str_seperator);
    $s.=") ";
    $s.="SELECT ";
    foreach ($arr_action as $key=>$value) {//compile where string
      $s.="`".$key."`".$str_seperator;
    }
    $s = rtrim($s, $str_seperator);
    $s.=" ";
    $s.="FROM ".$this->obj_parser_stmt->str_from." ";
    $s.="WHERE ";
    $s.=$this->obj_parser_stmt->str_where;
    $s.=";";
    $this->str_sql=$s;
    //$this->fn_echo("this->str_sql", $this->str_sql);
  }

  function fn_compile_sql_update(){
    if($this->bln_debug){$this->fn_echo("fn_compile_sql_update");}

    $arr_action=$this->fn_get_qualified_database_array($this->obj_parser_stmt->str_data_schema, $this->obj_parser_stmt->str_data_table, $this->arr_form);
    $str_seperator=", ";

    $s="";
    $s.="UPDATE ";
    $s.="`".$this->obj_parser_stmt->str_data_schema."`.`".$this->obj_parser_stmt->str_data_table."` ";
    $s.="SET ";
    foreach ($arr_action as $key=>$value) {//compile where string
      $s.="`".$key."`=".$value.$str_seperator;
    }
    $s = rtrim($s, $str_seperator);
    $s.=" ";
    $s.="WHERE `$this->str_id_record`='".$this->int_id_record."'";
    $s.=";";
    $this->str_sql=$s;
    //$this->fn_echo("this->str_sql", $this->str_sql);
  }

  function fn_compile_sql_delete(){
    if($this->bln_debug){$this->fn_echo("fn_compile_sql_delete");}

    $s="";
    $s.="DELETE ";
    $s.="FROM ";
    $s.="`".$this->obj_parser_stmt->str_data_schema."`.`".$this->obj_parser_stmt->str_data_table."` ";
    $s.="WHERE `$this->str_id_record`='".$this->int_id_record."'";
    $s.=";";
    $this->str_sql=$s;
    //$this->fn_echo("this->str_sql", $this->str_sql);
  }

  function fn_define_column_list($obj_parser_stmt=""){
    if($this->bln_debug){$this->fn_echo("START fn_define_column_list");}

    if(empty($obj_parser_stmt)){$obj_parser_stmt=$this->obj_parser_stmt;}

    $str_seperator=", ";
    $s="";

    $str_data_schema=$obj_parser_stmt->str_data_schema;
    $str_data_table=$obj_parser_stmt->str_data_table;
    if(empty($str_data_schema)){
      $str_data_schema=$this->con_schema;
    }
    $arr_action=$this->fn_get_qualified_database_array($str_data_schema, $str_data_table);
    $str_column_list="";
    foreach ($arr_action as $key=>$value) {//compile where string
      $str_column_list.="`".$str_data_schema."`.`".$str_data_table."`.`".$key."`".$str_seperator;
    }
    $str_column_list = rtrim($str_column_list, $str_seperator);
    $s.=$str_column_list;
    $s.=$str_seperator;
    //$this->fn_echo("s", "[".$s."]");

    if(isset($obj_parser_stmt->join)){
      $arr_join=$obj_parser_stmt->join;//Array of Expressions Objects
      //$this->fn_dump($arr_join);
      if(empty($arr_join)){}
      else{
        foreach ($arr_join as $key=>$value) {//compile where string
          $obj_exp=$value;
          $str_data_schema=$obj_exp->expr->database;
          $str_data_table=$obj_exp->expr->table;
          if(empty($str_data_schema)){
            $str_data_schema=$this->con_schema;
          }
          $arr_action=$this->fn_get_qualified_database_array($str_data_schema, $str_data_table);
          $str_column_list="";
          foreach ($arr_action as $key=>$value) {//compile where string
            $str_column_list.="`".$str_data_schema."`.`".$str_data_table."`.`".$key."`".$str_seperator;
          }
          $str_column_list = rtrim($str_column_list, $str_seperator);
          $s.=$str_column_list;
          $s.=$str_seperator;
          //$this->fn_echo("s", "[".$s."]");
        }
      }
    }
    //$this->fn_echo("s", "[".$s."]");
    $obj_parser_stmt->str_column_list=rtrim($s, $str_seperator);
    $obj_parser_stmt->arr_column_list=explode($str_seperator,$obj_parser_stmt->str_column_list);
    $obj_parser_stmt->bln_column_list=true;
    //$this->fn_echo("obj_parser_stmt->str_column_list", $obj_parser_stmt->str_column_list);
    if($this->bln_debug){$this->fn_echo("END fn_define_column_list");}
  }

  function fn_get_qualified_database_array($str_data_schema, $str_data_table, $arr_form=[]){

    $arr_action=[];
    $arr_column=[];

    if(empty($str_data_schema)){//left blank to show any obvious error
      //$str_data_schema=$this->con_schema;
    }

    $s="";
    foreach ($arr_form as $key=>$value) {//compile where string
      $arr_column[strtolower($key)]=$value;
      $s.="'$key', ";
    }
    $str_column = rtrim($s, ', ');

    $s="";
    $s.="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, COLUMN_KEY, EXTRA  ";
    $s.="FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='";
    $s.=$str_data_schema;
    $s.="' AND TABLE_NAME='";
    $s.=$str_data_table;
    $s.="'";
    $s.=" ";
    if(!empty($str_column)){
      $s.="AND COLUMN_NAME IN($str_column) ";
    }
    $s.="ORDER BY ORDINAL_POSITION;";
    $str_sql=$s;
    //$this->fn_echo("str_sql",$str_sql);
    $stmt = $this->obj_pdo->pdo->query($str_sql);
    while($row=$stmt->fetch(\PDO::FETCH_ASSOC)){

      $str_schema=$row["TABLE_SCHEMA"];
      $str_name=$row["COLUMN_NAME"];
      $str_name_lower=strtolower($str_name);

      $foo_value="";
      if(isset($arr_column[$str_name_lower])){//can be blank
        $foo_value=$arr_column[$str_name_lower];
      }

      /////////////////DISCARD IF NOT SPECIFIED, BUT ALLOW EXPLICIT EMPTY STRING
      $bln_write=true;
      switch ($foo_value){
        case "":
          if(!empty($arr_column)){
            $bln_write=false;
          }
        break;
        case "''":
          $foo_value="";
        break;
        case '""':
          $foo_value="";
        break;
      }
      /////////////////DISCARD IF NOT SPECIFIED, BUT ALLOW EXPLICIT EMPTY STRING

      /////////////////CHECK WRITE KEY
      $str_key=$row["COLUMN_KEY"];
      $str_extra=$row["EXTRA"];

      if(!empty($str_extra)){
        $bln_write=false;
      }
      if($str_key==="PRI"){
        $bln_write=false;
        if($this->bln_ignore_key_filter){
          if(!empty($foo_value) || $foo_value==="0"){// can write ids or zero
            $bln_write=true;
          }
        }
      }
      /////////////////CHECK WRITE KEY

      /////////////////CHECK NULL VALUE
      $bln_quote=true;
      if($foo_value==="NULL"){
        $bln_quote=false;
      }
      /////////////////CHECK NULL VALUE

      if($bln_write){
        if($bln_quote){
          $foo_value=$this->obj_pdo->pdo->quote($foo_value);
        }
        $arr_action[$row["COLUMN_NAME"]]=$foo_value;//add to array of ready values
      }
    }
    return $arr_action;
  }

  function fn_define_query($str_sql=""){

    $this->bln_debug_parser=false;
    //$this->bln_debug_parser=true;

    if(empty($str_sql)){
      $str_sql=$this->str_sql;
    }

    if($this->bln_debug_parser){$this->fn_echo("str_sql", "[".$str_sql."]");}

    $obj_parser = new Parser($str_sql);
    //fn_print($obj_parser);
    //fn_print_json($obj_parser);
    if (isset($obj_parser->statements[0])){
    $obj_parser_stmt=$obj_parser->statements[0];
    }
    else {
      $this->fn_write_container("You have an error with your SQL Syntax.");
      die();
    }
    //fn_print_json($obj_parser_stmt);
    //$this->fn_echo("<br>");
    $this->obj_parser_stmt=$obj_parser_stmt;
    $obj_parser_stmt->str_sql=$str_sql;
    $obj_parser_stmt->querytype="";
    $obj_parser_stmt->str_options="";
    $obj_parser_stmt->str_expr="";
    $obj_parser_stmt->str_from="";
    $obj_parser_stmt->str_data_schema="";
    $obj_parser_stmt->str_data_table="";
    $obj_parser_stmt->bln_column_list=false;
    $obj_parser_stmt->str_set="";
    $obj_parser_stmt->str_where="";
    $obj_parser_stmt->str_order="";
    $obj_parser_stmt->limit_offset="";
    $obj_parser_stmt->limit_row_count="";
    //fn_print($obj_parser_stmt);
    //fn_print_json($obj_parser_stmt);
    //$this->fn_dump($obj_parser_stmt);

    $arr_flags = Query::getFlags($obj_parser_stmt);
    if(isset($arr_flags)){
      //$obj_parser_stmt->$arr_flags=$this->arr;
      //fn_print($arr_expr);
      $obj_parser_stmt->querytype=$arr_flags['querytype'];
      if($this->bln_debug_parser){$this->fn_echo("obj_parser_stmt->querytype", "[".$obj_parser_stmt->querytype."]");}
    }

    if(isset($obj_parser_stmt->options)){
      $obj_options=$obj_parser_stmt->options;//Array of Expressions Objects
      $arr_options=$obj_options->options;//Array of Expressions Objects
      if(empty($arr_options)){}
      else{
        //fn_print($obj_options);
        //fn_print($arr_options);
        $obj_parser_stmt->str_options=implode(", ",$arr_options);
        if($this->bln_debug_parser){$this->fn_echo("obj_parser_stmt->str_options", "[".$obj_parser_stmt->str_options."]");}
      }
    }
    if(isset($obj_parser_stmt->expr)){
      $arr_expr=$obj_parser_stmt->expr;//Array of Expressions Objects
      if(empty($arr_expr)){}
      else{
        //fn_print($arr_expr);
        $obj_parser_stmt->str_expr=implode(", ",$arr_expr);
        if($this->bln_debug_parser){$this->fn_echo("obj_parser_stmt->str_expr", "[".$obj_parser_stmt->str_expr."]");}
      }
    }

    if(isset($obj_parser_stmt->from)){
      $arr_from=$obj_parser_stmt->from;//Array of Expressions Objects
      if(empty($arr_from)){}
      else{
        //fn_print($arr_from);
        $obj_parser_stmt->str_data_schema=$obj_parser_stmt->from[0]->database;
        $obj_parser_stmt->str_data_table=$obj_parser_stmt->from[0]->table;
        $obj_parser_stmt->str_from=implode(", ",$arr_from);
        if($this->bln_debug_parser){
          $this->fn_echo("obj_parser_stmt->str_from", "[".$obj_parser_stmt->str_from."]");
          $this->fn_echo("database", $obj_parser_stmt->str_data_schema);
          $this->fn_echo("table", $obj_parser_stmt->str_data_table);
        }
      }
    }
    if(isset($obj_parser_stmt->set)){
      $arr_set=$obj_parser_stmt->set;//Array of Expressions Objects
      if(empty($arr_set)){}
      else{
        //fn_print($arr_set);
        $obj_parser_stmt->str_set=implode(", ",$arr_set);
        if($this->bln_debug_parser){$this->fn_echo("obj_parser_stmt->str_set", "[".$obj_parser_stmt->str_set."]");}
      }
    }
    if(isset($obj_parser_stmt->where)){
      $arr_where=$obj_parser_stmt->where;//Array of Conditions Objects
      if(empty($arr_where)){}
      else{
        //fn_print($arr_where);
        $obj_parser_stmt->str_where=implode(" ",$arr_where);
        if($this->bln_debug_parser){$this->fn_echo("obj_parser_stmt->str_where", "[".$obj_parser_stmt->str_where."]");}
      }
    }
    if(isset($obj_parser_stmt->order)){
      $arr_order=$obj_parser_stmt->order;//Array of Conditions Objects
      if(empty($arr_order)){}
      else{
        //fn_print($arr_order);
        $obj_parser_stmt->str_order=implode(", ",$arr_order);
        if($this->bln_debug_parser){$this->fn_echo("obj_parser_stmt->str_order", "[".$obj_parser_stmt->str_order."]");}
      }
    }
    if(isset($obj_parser_stmt->limit)){
      $obj_limit=$obj_parser_stmt->limit;//Array of Expressions Objects
      if(empty($obj_limit)){}
      else{
        //fn_print($obj_limit);
        $obj_parser_stmt->limit_offset=$obj_limit->offset;
        $obj_parser_stmt->limit_row_count=$obj_limit->rowCount;
        if($this->bln_debug_parser){$this->fn_echo("obj_parser_stmt->limit_offset", "[".$obj_parser_stmt->limit_offset."]");}
        if($this->bln_debug_parser){$this->fn_echo("obj_parser_stmt->limit_row_count", "[".$obj_parser_stmt->limit_row_count."]");}
      }
    }

    //*
    if(isset($obj_parser_stmt->join)){
      $arr_join=$obj_parser_stmt->join;//Array of Expressions Objects
      //$this->fn_dump($arr_join);
      if(empty($arr_join)){}
      else{
        //fn_print($arr_join);
        /*
        $obj_parser_stmt->str_join=implode(", ",$arr_join);
        if($this->bln_debug_parser){
          $this->fn_echo("obj_parser_stmt->str_join", "[".$obj_parser_stmt->str_join."]");
          //$this->fn_echo("database", $obj_parser_stmt->str_data_schema);
          //$this->fn_echo("table", $obj_parser_stmt->str_data_table);
        }
        //*/
      }
    }
    //*/
    //$this->fn_debug_parser();
    return $obj_parser_stmt;
  }
  function fn_debug_parser($obj_parser_stmt=""){

    $this->fn_echo("<BR>");
    if($this->bln_debug){$this->fn_echo(">>>>>>>>>>>fn_debug_parser");}

    if(empty($obj_parser_stmt)){
      $obj_parser_stmt=$this->obj_parser_stmt;
    }

    $this->fn_print_json($obj_parser_stmt);
    $this->fn_echo("<br>");

    $this->fn_echo("obj_parser_stmt str_sql", "[".$obj_parser_stmt->str_sql."]");
    $this->fn_echo("obj_parser_stmt->querytype", "[".$obj_parser_stmt->querytype."]");
    $this->fn_echo("obj_parser_stmt->str_options", "[".$obj_parser_stmt->str_options."]");
    $this->fn_echo("obj_parser_stmt->str_expr", "[".$obj_parser_stmt->str_expr."]");
    if(isset($obj_parser_stmt->expr)){
    $this->fn_print($arr_expr=$obj_parser_stmt->expr);
    }
    $this->fn_echo("obj_parser_stmt->str_from", "[".$obj_parser_stmt->str_from."]");
    $this->fn_echo("obj_parser_stmt->str_data_schema", $obj_parser_stmt->str_data_schema);
    $this->fn_echo("obj_parser_stmt->str_data_table", $obj_parser_stmt->str_data_table);
    if($obj_parser_stmt->bln_column_list){
      $this->fn_echo("obj_parser_stmt->str_column_list", "[".$obj_parser_stmt->str_column_list."]");
      //$this->fn_dump($obj_parser_stmt->arr_column_list);
    }
    $this->fn_echo("obj_parser_stmt->str_set", "[".$obj_parser_stmt->str_set."]");
    $this->fn_echo("obj_parser_stmt->str_where", "[".$obj_parser_stmt->str_where."]");
    $this->fn_echo("obj_parser_stmt->str_order", "[".$obj_parser_stmt->str_order."]");
    $this->fn_echo("obj_parser_stmt->limit_offset", "[".$obj_parser_stmt->limit_offset."]");
    $this->fn_echo("obj_parser_stmt->limit_row_count", "[".$obj_parser_stmt->limit_row_count."]");

    if($this->bln_debug){$this->fn_echo(">>>>>>>>>>fn_debug_parser");}
    $this->fn_echo("<BR>");
  }
  function fn_debug_expr_object($obj_expr){
    $this->fn_echo("expr->database", $obj_expr->database);
    $this->fn_echo("expr->table", $obj_expr->table);
    $this->fn_echo("expr->column", $obj_expr->column);
    $this->fn_echo("expr->expr", $obj_expr->expr);
    $this->fn_echo("expr->alias", $obj_expr->alias);
    $this->fn_echo("expr->function", $obj_expr->function);
    $this->fn_echo("expr->subquery", $obj_expr->subquery);
  }
  function fn_fetch_column($int_index=0){
    $this->fn_record_action();
    return  $this->stmt->fetchColumn($int_index);
  }
  function fn_record_action(){
    //$this->fn_echo($this->str_sql);
    $this->fn_open_rs();
    $this->fn_initialize_var();
  }
  function fn_view_script_record($str_name_table, $int_id_record){
    $obj_my=$this->obj_my;
    $this->str_sql="SELECT * FROM $obj_my->SchemaName.$str_name_table where id=$int_id_record;";
    $this->fn_records_view();
  }
  function fn_get_last_insert_id(){
    return $this->obj_pdo->pdo->lastInsertId();
  }
  function fn_get_schema_exist($str_name_schema){
    $this->str_sql="SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$str_name_schema'";
    return $this->fn_fetch_column();
  }
  function fn_get_table_exist($str_name_schema, $str_name_table){
    $this->str_sql="SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$str_name_schema' AND TABLE_NAME='$str_name_table'";
    return $this->fn_fetch_column();
  }
}//END CLASS DATA
?>
