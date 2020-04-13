class cls_data {
  constructor(obj_ini) {

    this.obj_ini=obj_ini;
    this.obj_tag=$(obj_ini.str_tag);
    this.bln_debug=false;
    this.str_form_var="";
    this.int_num_row_per_page_default="5";
    this.int_zindex=1;
    this.obj_theme=new ThemeData();
    this.searchParams;
    this.obj_post=new Object();
    //this.str_url_base="/data-xplorer/_server/get_data.php";
    this.str_url_base="/"+obj_ini.str_tag+"/instance.php";

    /*
    var str_host=document.location.host.replace(/\W/g, '');
    if(this.obj_ini.arr_con==undefined){this.obj_ini.arr_con=[];}
    this.obj_ini.arr_con.forEach(function(obj_ini) {
      obj_ini.str_name=str_host+'-'+obj_ini.str_name;
    });
    //*/
    this.str_ext_pills="-pills";
    this.str_ext_collapse_con="-collpase-con";
    this.str_ext_con_details_form="-con-details-form";
    this.str_ext_host="-host";
    this.str_ext_user="-user";
    this.str_ext_pass="-pass";
    this.str_ext_schema="-schema";
    this.str_ext_connect="-connect";
    this.str_ext_query_form="-query-form";
    this.str_ext_query="-query";

    this.fn_reset_con_setting();
  }
  fn_reset_con_setting(){
    this.obj_post=new Object();
    var obj_post=this.obj_post;
    obj_post.str_con_host="";
    obj_post.str_con_user="";
    obj_post.str_con_pass="";
    obj_post.str_data_schema="";
    obj_post.str_data_query="";
  }
  fn_execute(){
    this.fn_write_interface();
    if (!this.obj_ini.bln_data_menu){
        this.fn_records_view();
    }
  }
  fn_reset_nav(){
    this.str_nav_cmd="nav-reset";
  }
  fn_change_row(int_num_row_per_page){
    this.int_num_row_per_page=int_num_row_per_page;
    this.str_nav_cmd="nav-num-row";
    this.fn_records_view();
  }
  fn_open_nav(str_nav_cmd){
    this.str_nav_cmd=str_nav_cmd;
    this.fn_records_view();
  }
  fn_open_search(str_id_form){
    this.str_form_var=$("#"+str_id_form ).serialize();
    this.fn_records_view();
  }
  fn_open_record(str_id_form, str_action){
    this.str_data_action=str_action;;
    this.str_form_var=$("#"+str_id_form ).serialize();
    this.obj_search.val("");
    this.fn_open();
  }
  fn_btn_pill_onclick(str_tag){
    this.str_tag_con=str_tag;//causes conneciton to be taken form the correct set of conneciton inputs
    $("#data-list-schema").select2('destroy');
    $("#data-list-schema").remove();
    $("#data-list-table").remove();
    $("[data-count-process]").val("");
    $("[data-default-schema]").val("");
    $("data-count-process").html("");

    this.obj_query=$("#"+str_tag+this.str_ext_query);
    this.obj_search=$("[input-srh]");
    this.obj_button_connect=$("#"+str_tag+this.str_ext_connect);

    this.obj_con_host=$("#"+str_tag+this.str_ext_host);
    this.obj_con_user=$("#"+str_tag+this.str_ext_user);
    this.obj_con_pass=$("#"+str_tag+this.str_ext_pass);
    this.obj_con_schema=$("#"+str_tag+this.str_ext_schema);

    this.obj_query.val("");
    this.obj_search.val("");
    this.str_form_var="";

    $('[nav-pane]').collapse("show");
    this.fn_close(true);
  }
  fn_btn_connect_onclick(){
    this.obj_search.val("");
    this.str_form_var="";
    this.fn_reset_nav();
    this.fn_connect();
  }
  fn_connect(){

    if(this.bln_debug){this.fn_debug("fn_connect")};

    var int_return=this.fn_records_view();

    var bln_error=false;
    var str_error="";
    if(int_return){bln_error=true;}
    switch(int_return){
      case(1):
        str_error="Host not Set";
        break;
      case(2):
        str_error="User not Set";
        break;
      case(3):
        str_error="Password not Set";
        break;
      case(4):
        //str_error="Default Schema not Set";
        break;
    }
    if(int_return>0 && int_return<100){
      this.obj_modal.fn_call(str_error);
      $("#"+this.str_tag_con+"-connect").prop('disabled', false);
    }
  }
  fn_query_onkeyup(e){
    if (e.ctrlKey && e.keyCode == 13) {
        this.obj_button_connect.click();
    }
  }
  fn_write_navbar(){
    var that=this;
    this.fn_write_navbar_top();
    this.fn_write_navbar_btm();
    $("[pill-link]").on("click", function(e){
      e.preventDefault();
      that.fn_btn_pill_onclick($(this).attr('tag-name'));
    });
    $("[btn-connect]").on( "click", function(e) {
      e.preventDefault();
      that.fn_btn_connect_onclick();
    });
    $("[data-query]").on( "keyup", function(e) {
      that.fn_query_onkeyup(e);
    });
    $("[frm-srh]").on( "submit", function(e) {
      e.preventDefault();
      that.fn_open_search($(this).attr('id'));
    });
    $("[nav-new]").on( "click", function(e) {
        that.fn_open_record("new", "records-new");
    });
    $("[nav-srt]").on( "click", function(e) {
        that.fn_open_nav("nav-srt");
    });
    $("[nav-bck]").on( "click", function(e) {
        that.fn_open_nav("nav-bck");
    });
    $("[nav-fwd]").on( "click", function(e) {
        that.fn_open_nav("nav-fwd");
    });
    $("[nav-end]").on( "click", function(e) {
        that.fn_open_nav("nav-end");
    });
    $("[nav-row]").on( "change", function(e) {
        var int_num_row_per_page=$("[nav-row] option:selected").val();
        that.fn_change_row(int_num_row_per_page);
    });
  }
  fn_list_schema_on_change(){
    if(this.bln_debug){this.fn_debug("list-schema change")};
    this.obj_list_table=$("#data-list-table");
    this.obj_list_table.remove();
    this.obj_post.str_data_table=undefined;
    var str_text_data_schema=$("#data-list-schema option:selected").val();
    //$("#"+this.str_tag_con+"-schema").val(str_text_data_schema);
    this.obj_search.val("");
    this.str_form_var="";
    this.fn_records_view();
  }
  fn_list_table_on_change(){
    if(this.bln_debug){this.fn_debug("list-table change")};
    var str_text=this.obj_query.val();
    var str_text_data_schema=$("#data-list-schema option:selected").val();
    var str_text_data_table=$("#data-list-table option:selected").val();
    var str_sql='SELECT * FROM `'+str_text_data_schema+'`.`'+str_text_data_table+'`;';
    this.obj_query.val(str_sql);
    this.obj_search.val("");
    this.str_form_var="";
    this.fn_reset_nav();
    this.fn_records_view();
  }
  fn_list_schema_on_open(){
    var that=this;
    this.fn_on_connect("schema");

    //*
    this.obj_list_schema=$("#data-list-schema");
    this.obj_list_schema.select2();
    if(typeof this.obj_list_schema.html()== "undefined"){return;}//check to see there is a control created
    this.obj_list_schema.on( "change", function(e) {
      that.fn_list_schema_on_change();
    });
    this.fn_list_schema_on_change();
    //*/
  }
  fn_list_table_on_open(){
    var that=this;
    this.fn_on_connect("table");
    this.obj_list_table=$("#data-list-table");
    if(typeof this.obj_list_table.html()== "undefined"){return;}//check to see there is a control created
    this.obj_list_table.on( "change", function(e) {
      that.fn_list_table_on_change();
    });
    this.fn_list_table_on_change();
  }
  fn_records_view(){
    this.str_data_action="records-view";
    return this.fn_open();
  }
  fn_disable_controls(bln_val){
    $('[nav-face]').prop('disabled', bln_val);
    $("record-msg").html("");
  }
  fn_format_url() {
    if(this.bln_debug){this.fn_debug("fn_format_url")};

    this.str_url=this.str_url_base;

    var obj_post = this.obj_post;

    obj_post.nav_cmd=this.str_nav_cmd;
    this.str_nav_cmd="";
    obj_post.row_per_page=this.int_num_row_per_page;
    obj_post.formvar=this.str_form_var;

    obj_post.action="";
    obj_post.str_id_table_record="";
    obj_post.str_id_record="";
    obj_post.int_id_record="";

    obj_post.tag=this.obj_ini.str_tag; //obj_post.tag This relates to data router class type and should not be changed.


    if(this.str_data_action!=""){
        obj_post.action=this.str_data_action;
    }
    if(typeof this.int_id_record !== "undefined"){
        obj_post.str_id_table_record=this.str_id_table_record;
        obj_post.str_id_record=this.str_id_record;
        obj_post.int_id_record=this.int_id_record;
    }
  }

  fn_open(){
    if(this.bln_debug){this.fn_debug("fn_open")};

    var that=this;

    this.fn_close();
    this.fn_allow_connection(false);

    if(this.obj_ini.bln_data_menu){
      var int_return=this.fn_get_data_menu();
      if (int_return){
        this.fn_allow_connection(true);
        return int_return;
      }
    }

    this.fn_format_url();

    if(this.bln_debug){this.fn_debug(this.str_url)};

    this.obj_pad.load(this.str_url, this.obj_post, function(){
      that.fn_on_open();
    });
    return 0;
  }

  fn_get_data_menu(){
    var obj_post=this.obj_post;
    var that=this;
    this.fn_get_con();
    if(obj_post.str_con_host==""){return 1;}
    if(obj_post.str_con_user==""){return 2;}
    if(obj_post.str_con_pass==""){return 3;}
    if(obj_post.str_con_pass=="undefined"){return 3;}
    //if(obj_post.str_con_schema==""){return 4;}

    if(this.obj_ini.bln_navbar_top){
      if(typeof(obj_post.str_data_schema)=="undefined"){
        this.str_data_action="get-list-schema";
        this.fn_format_url();
        $("#data-tag-schema").load(this.str_url, this.obj_post, function(){
          that.fn_list_schema_on_open();
        });
        this.str_data_action="";
        return 100;
      }
      if(typeof(obj_post.str_data_table)=="undefined"){
        //if(obj_post.str_data_schema!=""){
          this.str_data_action="get-list-table";
          this.fn_format_url();
          $("#data-tag-table").load(this.str_url, this.obj_post, function(){
            that.fn_list_table_on_open();
          });
        //}
        this.str_data_action="";
        return 100;
      }
    }
    return 0;
  }
  fn_get_con(){
    var obj_post=this.obj_post;
    if(this.obj_ini.bln_data_menu){
      obj_post.str_con_host=this.obj_con_host.val();
      obj_post.str_con_user=this.obj_con_user.val();
      obj_post.str_con_pass=this.obj_con_pass.val();
      obj_post.str_con_schema=this.obj_con_schema.val();
      obj_post.str_data_query=this.obj_query.val();

      obj_post.str_data_schema=$("#data-list-schema option:selected").val();
      obj_post.str_data_table=$("#data-list-table option:selected").val();
    }
  }
  fn_allow_connection(bln_allow){
    var bln_disabled=false;
    if(!bln_allow){
      bln_disabled=true;
    }
    $("#"+this.str_tag_con+"-connect").prop('disabled', bln_disabled);
  }

  fn_on_connect(tag){

    var str_data_default_schema=$("["+tag+"-default-schema]").val();
    if(str_data_default_schema==undefined){
      str_data_default_schema="";
      return;
    }
    var int_data_count_process=$("["+tag+"-count-process]").val();
    if(int_data_count_process==undefined){
      int_data_count_process="0";
      return;
    }
    var str_message_pool=int_data_count_process +" pool";
    if(str_data_default_schema==""){
      str_data_default_schema="shared";
    }
    str_message_pool+=" [" + str_data_default_schema + "]";
    $("data-count-process").html(str_message_pool);
    //alert(tag + " int_data_count_process: " + int_data_count_process);
  }

  fn_on_open(){

    if(this.bln_debug){this.fn_debug("fn_on_open")};

    this.fn_disable_controls(false);
    this.fn_allow_connection(true);
    this.fn_on_connect("data");
    //Set Page Varaibles
    this.str_action=$("[data-action]").val();//linked to server
    this.str_data_message=$("[data-message]").val();
    //Set Page Varaibles

    //Disable Selected Controls
    var bln_disable_nav_start=$("[data-disable-nav-start]").val();
    $("[nav-bck]").prop('disabled', bln_disable_nav_start);
    $("[nav-srt]").prop('disabled', bln_disable_nav_start);

    var bln_disable_nav_end=$("[data-disable-nav-end]").val();
    $("[nav-fwd]").prop('disabled', bln_disable_nav_end);
    $("[nav-end]").prop('disabled', bln_disable_nav_end);

    var bln_disable_new=$("[data-disable-new]").val();
    $("[nav-new]").prop('disabled', bln_disable_new);

    $("nav-records").hide();
    $("record-msg").html("");
    switch(this.str_action){
      case("records-view"):
        $("nav-records").show();
        $("record-msg").html(this.str_data_message);
      break;
    }
    //Disable Selected Controls

    //this.obj_pad.sortable({axis: "y"});
    //this.obj_pad.disableSelection();
    /*
    $("div.d-flex").draggable();
    $("div.d-flex").on( "mousedown", function(e) {
      this.style.zIndex=that.int_zindex++;
    });
    //*/

    var that=this;
    $("[frm-record]").on( "submit", function(e) {
      if(that.bln_debug){that.fn_debug("frm-record")};
      e.preventDefault();
      var obj_dom_target=e.target;
      var int_id_frm=obj_dom_target.getAttribute("id");
      var int_id_submit=int_id_frm+"-submit";

      var obj_frm_submit=document.getElementById(int_id_submit);
      that.int_id_record=obj_frm_submit.getAttribute("record-id");
      that.str_id_record=obj_frm_submit.getAttribute('record-id-name');
      that.str_id_table_record=obj_frm_submit.getAttribute('record-id-table');
      that.fn_open_record($(this).attr('id'), $(this).attr('frm-action'));
    });

    $("[record-btn]").on( "click", function(e) {
        if(that.bln_debug){that.fn_debug("record-btn 'record' on click")};
        that.int_id_record=$(this).attr('record-id');
        that.str_id_record=$(this).attr('record-id-name');
        that.str_id_table_record=$(this).attr('record-id-table');
        that.fn_open_record($(this).attr('form-id'), $(this).attr('frm-action'));
    });

  }
  fn_close(bln_reset) {
    this.fn_disable_controls(true);
    this.obj_pad.empty();
    //to do find a way of nuking the jquery pad events.
    if(bln_reset){
      this.fn_reset_nav();
    }
  }
  fn_debug(str_debug){
    console.log(str_debug);
  }
  ///////////////////////////////////////////////WRITE INTERFACE
  fn_write_interface(){

    if((typeof(this.obj_ini.bln_navbar_top))=="undefined"){this.obj_ini.bln_navbar_top=true;};
    if((typeof(this.obj_ini.bln_data_menu))=="undefined"){this.obj_ini.bln_data_menu=false;};
    this.obj_tag.append('<data-nav-cmb></data-nav-cmb>');
    this.obj_nav_cmb=$('data-nav-cmb');
    this.obj_tag.append('<data-nav-con></data-nav-con>');
    this.obj_nav_con=$('data-nav-con');
    this.obj_tag.append('<data-nav-schema></data-nav-schema>');
    this.obj_nav_schema=$('data-nav-schema');
    this.obj_tag.append('<page-modal></page-modal>');
    this.obj_modal=new cls_modal();
    this.obj_modal.fn_root("page-modal");

    this.fn_write_navbar_cmb();
    if(this.obj_ini.bln_data_menu){
      this.fn_write_navbar_con();
    }

    this.obj_tag.append('<data-nav-top></data-nav-top>');
    this.obj_nav_top=$('data-nav-top');
    this.obj_tag.append('<data-pad></data-pad>');
    this.obj_pad=$('data-pad');
    this.obj_tag.append('<data-nav-bottom></data-nav-bottom>');
    this.obj_nav_btm=$('data-nav-bottom');

    this.fn_write_navbar();
  }
  fn_write_navbar_cmb(){
    var str_html, s;
    s='';
    s+='<nav aria-label="breadcrumb">';
    s+='<ol class="breadcrumb">';
    s+='<li class="breadcrumb-item"><a class="" href="/" style="margin-right:3px">Home</a>';
    s+='</li><li class="breadcrumb-item"><a class="active" href="/page/'+this.obj_ini.str_tag+'" style="margin-right:3px">'+this.obj_ini.str_tag+'</a>';
    s+='</li></ol>';
    s+='</nav>';
    str_html=s;
    this.obj_nav_cmb.append(str_html);
  }
  fn_write_navbar_con(){
    var str_html, s;
    this.obj_nav_con.append('<connection-holder class="form-inline"></connection-holder>');
    this.obj_con_holder=$('connection-holder');

    this.obj_con_holder.append('<ul pill-holder class="nav nav-pills mb-1" id="pills-tab" ></ul>');
    this.obj_pill_holder=$('[pill-holder]');

    this.obj_con_holder.append('<div tab-content class="tab-content" id="pills-tabContent"></div>');
    this.obj_tab_content=$('[tab-content]');

    var that=this;


    this.obj_ini.arr_con.forEach(function(obj_ini) {
      that.obj_pill_holder.append(that.fn_write_con_button(obj_ini));
    });
    this.obj_ini.arr_con.forEach(function(obj_ini) {
      that.obj_tab_content.append(that.fn_write_con_body(obj_ini));
    });

    s="";
    s+='<div class="form-inline">';
      s+='<form id="data-tag-schema" class="mr-1" method="post"></form>';
      s+='<form id="data-tag-table" class="mr-1" method="post"></form>';
    s+='</div>';
    this.obj_nav_schema.append(s);
    this.obj_con_holder.append(this.obj_theme.fn_get_theme_pills());
  }
  fn_write_con_button(obj_ini){

    var str_name;

    if(!obj_ini.str_title){
      obj_ini.str_title=obj_ini.str_name;
    }
    var s='';
    //s+='<li class="nav-item" data-toggle="collapse" data-target="#'+obj_ini.str_name+'-collpase-con">';
    s+='<li class="nav-item">';
    str_name=obj_ini.str_name+this.str_ext_pills;
    s+='<a pill-link href="#'+str_name+'" tag-name="'+obj_ini.str_name+'" class="nav-link mr-1 '+this.obj_theme.str_theme_color+'" data-toggle="pill" >';
    s+=obj_ini.str_title;
    s+='</a>';
    s+='</li>';
    return s;


  }

  fn_write_con_body(obj_ini){
    var s='';

    var str_name;
    var str_value;

    str_name=obj_ini.str_name+this.str_ext_pills;
    s+='<div class="tab-pane mb-1" id="'+str_name+'">';
    str_name=obj_ini.str_name+this.str_ext_collapse_con;
    s+='<div nav-pane class="collapse show mb-0" id="'+str_name+'">';
    s+='<div class="card card-body">';
    str_name=obj_ini.str_name+this.str_ext_con_details_form;
    s+='<form id="'+str_name+'" name="'+str_name+'" class="form-inline mb-1" method="post">';

    str_value=obj_ini.str_host;
    str_name=obj_ini.str_name+this.str_ext_host;
    s+='<input type="text" class="form-control mb-1 mr-1" placeholder="Host..." value="'+str_value+'" id="'+str_name+'" name="'+str_name+'">';
    str_value=obj_ini.str_user;
    str_name=obj_ini.str_name+this.str_ext_user;
    s+='<input type="text" class="form-control mb-1 mr-1" placeholder="User..." value="'+str_value+'" id="'+str_name+'" name="username" autocomplete="username">';
    str_value=obj_ini.str_password;
    str_name=obj_ini.str_name+this.str_ext_pass;
    s+='<input type="password" class="form-control mb-1 mr-1" placeholder="Pass..." value="'+str_value+'" id="'+str_name+'" name="password" autocomplete="current-password">';
    str_value=obj_ini.str_schema;
    str_name=obj_ini.str_name+this.str_ext_schema;
    s+='<input type="text" class="form-control mb-1 mr-1" placeholder="Database..." value="'+str_value+'" id="'+str_name+'" name="'+str_name+'">';
    str_name=obj_ini.str_name+this.str_ext_connect;
    s+='<button btn-connect tag-name="'+obj_ini.str_name+'" id="'+str_name+'" class="btn mb-1 mr-1 btn-'+this.obj_theme.str_theme_color+'">';
    s+='Run';
    s+='</button>';
    s+='<data-count-process class="m-2"></data-count-process>';


    s+='<div class="w-100 h-100" style="height:300px">';
    str_name=obj_ini.str_name+this.str_ext_query_form;
    s+='<form id="'+str_name+'" name="'+str_name+'" class="mr-1" method="post">';
    str_name=obj_ini.str_name+this.str_ext_query;
    s+='<textarea id="'+str_name+'" name="'+str_name+'" data-query class="mb-1 w-100 mh-100"  style="height:100px">';
    s+='</textarea>';
    s+='</form>';
    s+='</div>';

    s+='</form>';

    s+='</div>';
    s+='</div>';

    s+='</div>';

    return s;
  }
  fn_write_navbar_top(){
    if(!this.obj_ini.bln_navbar_top){return;}
    var obj_ini = new Object();
    obj_ini.form_search_id = "frm-srh-nav-top";
    obj_ini.bln_search = true;
    obj_ini.bln_new=true;
    obj_ini.bln_nav = true;
    obj_ini.obj_container = this.obj_nav_top;
    this.fn_write_nav_control(obj_ini);
  }

  fn_write_nav_control(obj_ini){
    var str_html, s;
    s='';
    s+='<form frm-srh nav-face class="form-inline mt-2" id="'+obj_ini.form_search_id+'" style="">';
    if(obj_ini.bln_search){
        s+='<input disabled type="text" input-srh nav-face class="form-control mb-1 mr-1" placeholder="search here..." value="" name="data-srh">';//linked to server
        s+='<button disabled type="submit" nav-srh nav-face class="btn btn-'+this.obj_theme.str_theme_color+' mb-1 mr-1" form-id="'+obj_ini.form_search_id+'">Search</button>';
    }
    if(obj_ini.bln_new){
    s+='<button disabled nav-new nav-face class="btn btn-'+this.obj_theme.str_theme_color+' mb-1 mr-1">New</button>';
    }
    s+='<nav-records>';
    if(obj_ini.bln_nav){
      s+='<select disabled nav-row nav-face class="form-control mb-1 mr-1">';
      s+=this.fn_get_select_option("1", this.int_num_row_per_page_default);
      s+=this.fn_get_select_option("5", this.int_num_row_per_page_default);
      s+=this.fn_get_select_option("10", this.int_num_row_per_page_default);
      s+=this.fn_get_select_option("25", this.int_num_row_per_page_default);
      s+=this.fn_get_select_option("100", this.int_num_row_per_page_default);
      s+='</select>';
      s+='<button disabled nav-srt nav-face class="btn btn-'+this.obj_theme.str_theme_color+' mb-1 mr-1"><<</button>';
      s+='<button disabled nav-bck nav-face class="btn btn-'+this.obj_theme.str_theme_color+' mb-1 mr-1"><</button>';
      s+='<button disabled nav-fwd nav-face class="btn btn-'+this.obj_theme.str_theme_color+' mb-1 mr-1">></button>';
      s+='<button disabled nav-end nav-face class="btn btn-'+this.obj_theme.str_theme_color+' mb-1 mr-1">>></button>';
      s+='<record-msg class="m-2"></record-msg>';

    }
    s+='</nav-records>';
    s+='</form>';
    str_html=s;
    $(obj_ini.obj_container).append(str_html);

    this.obj_search=$("[input-srh]");
  }
  fn_get_select_option(str_val, str_selected, str_text){
    if(str_text===undefined){str_text=str_val;}
    var s='';
    s+='<option value="'+str_val+'" ';
    if(str_val==str_selected){
      s+='selected'
    }
    s+='>'
    s+=str_text
    s+='</option>';
    return s;
  }
  fn_write_navbar_btm(){
    if(!this.obj_ini.bln_navbar_btm){return;}
    var obj_ini = new Object();
    obj_ini.form_search_id = "frm-srh-nav-btm";
    obj_ini.obj_theme = this.obj_theme;
    obj_ini.bln_search = false;
    obj_ini.bln_new=false;
    obj_ini.bln_nav = true;
    obj_ini.obj_container = this.obj_nav_btm;
    this.fn_write_nav_control(obj_ini);
  }
  ///////////////////////////////////////////////WRITE INTERFACE
}//END CLASS DATA
class ThemeData{
  constructor() {
    this.str_border_color='border-primary';
    this.str_border_size='';
    this.str_border_rounded='rounded';
    this.str_theme="dark";
    this.str_theme_color=this.str_theme;
    this.str_text_color="text-white";
    this.str_color="white";

    this.str_bg_color="bg-"+this.str_theme_color;
    //this.str_border="border";
    this.str_border="";
    this.str_shadow="shadow";
    this.str_class=this.str_bg_color+' '+this.str_text_color+' '+this.str_border_rounded+' '+this.str_border+' '+this.str_border_color+' '+this.str_shadow;

    this.blue="#007bff";this.indigo="#6610f2";this.purple="#6f42c1";this.pink="#e83e8c";this.red="#dc3545";this.orange="#fd7e14";this.yellow="#ffc107";this.green="#28a745";this.teal="#20c997";this.cyan="#17a2b8";this.white="#fff";this.gray="#6c757d";this.gray_dark="#343a40";this.primary="#007bff";this.secondary="#6c757d";this.success="#28a745";this.info="#17a2b8";this.warning="#ffc107";this.danger="#dc3545";this.light="#f8f9fa";this.dark="#343a40";

    switch (this.str_theme) {case "blue":this.theme_color=this.blue;break;case"indigo":this.theme_color=this.indigo;break;case"purple":this.theme_color=this.purple;break;case"pink":this.theme_color=this.pink;break;case"red":this.theme_color=this.red;break;case"orange":this.theme_color=this.orange;break;case"yellow":this.theme_color=this.yellow;break;case"green":this.theme_color=this.green;break;case"teal":this.theme_color=this.teal;break;case"cyan":this.theme_color=this.cyan;break;case"white":this.theme_color=this.white;
    break;case"gray":this.theme_color=this.gray;break;case"gray_dark":this.theme_color=this.gray_dark;break;case "primary":this.theme_color=this.primary;break;case"secondary":this.theme_color=this.secondary;break;case"success":this.theme_color=this.success;break;case"info":this.theme_color=this.info;break;case"warning":this.theme_color=this.warning;break;case"danger":this.theme_color=this.danger;break;case "light":this.theme_color=this.light;break;case
    "dark":this.theme_color=this.dark;break;default:this.theme_color=this.cyan;
    }
  }
  fn_get_theme_pills() {
    //return '<style>.nav-pills > li > a.active {background-color: '.$str_color.'!important;}</style>';
    var s="";
    s+="<style>";
    s+="a{color:black;text-decoration:none;}";
    s+="a:hover{color:black;}";
    s+=".nav-item > a.active {background-color: "+this.theme_color+"!important;} ";
    //s+=".nav-link > a{text-decoration:none;}";
    //s+=".recordtable {width: inherit;}";
    //s+=".recordlabel {text-align:right;width:10%;}";
    //s+=".my-max {max-width: 100%;height: auto !important;}";
    s+="</style>";
    return s;
  }

}
