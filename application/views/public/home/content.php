        

<!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40">
          <!-- BEGIN CONTENT -->
          <div class="col-md-12 col-sm-12">
            <div class="content-page">
              <?php if(isset($title)){ ?>
                <div class="row">
                  <!-- BEGIN LEFT SIDEBAR -->            
                  <div class="col-md-9 col-sm-9 blog-posts">
                    <h2><?=$title?></h2>
                  </div>
                </div>
              <?php } ?>
              <div class="row">
                <!-- BEGIN LEFT SIDEBAR -->            
                <div class="col-md-9 col-sm-9 blog-posts">
                  <?php if($page==1){ ?>
                    <!-- carousel -->
                    <div class="row">
                      <div class="col-md-12">
                        <div class="front-carousel">
                            <div id="myCarousel" class="carousel slide">
                              <!-- Carousel items -->
                              <div class="carousel-inner">
                                <?php foreach ($slide_db as $key => $value) {?>
                                  <?php $active = $key<1?"active":""; ?>
                                  <div class="item <?=$active?>">
                                    <img width="100%"  class="img-responsive" src="<?=base_url($value["img"])?>" alt="<?=isset($data["alt"])?$data["alt"]:""?>" title="<?=isset($data["alt"])?$data["alt"]:""?>">
                                    <a href="<?=base_url('read/'.$value["id"]."-".str_ireplace(" ",".",strtolower(trim($value["title"]))))?>" class="carousel-caption slide-title" >
                                      <?=$value["title"]?>
                                    </a>
                                  </div>
                                <?php } ?>
                              </div>
                            </div>                
                        </div>
                      </div>
                    </div>
                    <hr class="blog-post-sep">
                    <!-- end carousel -->
                  <?php } ?>
                  <div class="row-news">
                    <?php foreach ($news_db as $key => $value) {?>

                      <div class="media-home"> 
                        <div class="media-left"> 
                          <a href="<?=base_url('read/'.$value["id"]."-".str_ireplace(" ",".",strtolower($value["title"])))?>"> 
                            <img class="media-object" data-src="holder.js/64x64" alt="64x64" src="<?=base_url($value['img'])?>" data-holder-rendered="true" style="width: 119px; height: 59px;"> 
                          </a> 
                        </div> 
                        <div class="media-body"> 
                          <h4 class="media-heading">
                            <a href="<?=base_url('read/'.$value["id"]."-".str_ireplace(" ",".",strtolower(trim($value["title"]))))?>"> <?=$value["title"]?></a>
                          </h4> 
                          <div class="media-info">
                            <?=$value["date"]?>
                          </div>
                        </div> 
                      </div>
                      <hr class="blog-post-sep" style="margin-bottom: 0px;margin-top: 5px;">

                    <?php } ?>
                  </div>

                  <?php if(isset($load_more) && $load_more==1 ){ ?>
                    <div class="row btn-load-more">
                        <div class="col-md-12 btn-load-more text-center">
                            <button class="btn blue btn-block btn-sm dropdown-toggle btn-side-home-grey" type="button"  id="btn-load-more">
                              BERITA SELANJUTNYA
                            </button>
                        </div>
                    </div>
                  <?php } ?>     
                </div>
                <!-- END LEFT SIDEBAR -->

                <!-- BEGIN RIGHT SIDEBAR -->            
                <div class="col-md-3 col-sm-3 blog-sidebar">
                  <?=$side_content?>
                </div>
                <!-- END RIGHT SIDEBAR -->          
              </div>
            </div>
          </div>
          <!-- END CONTENT -->
        </div>
<!-- END SIDEBAR & CONTENT -->

<script type="text/javascript">
$(function () {
    function get_datas (page) {
      $("#btn-load-more").html('Loading');
      $("#btn-load-more").attr('disabled','disabled');
      var menu_id = '<?=$menu_id?>';
      var url = "<?=base_url('content/get_datas?page=')?>"+page+'&menu_id='+menu_id;
      var result_data = $.api_get(url);
      var data = "";
      if(result_data.load_more == 0){
        $(".btn-load-more").hide();
      }

      for (var i = 0; i < result_data.length; i++) {
        data += template(result_data[i]);
      };
      $(".row-news").append(data);

      $("#btn-load-more").html('BERITA SELANJUTNYA');
      $("#btn-load-more").removeAttr('disabled');
      $("#btn-load-more").blur();
    }

    function template (data) {
      var template = "";
      var base_url = '<?=base_url()?>';
      var title = String(data.title);
      var title_link = title.split(' ');
      title_link = title_link.join('.');

      var href = base_url+'read/'+data.id+'-'+title_link; 
      var img = base_url+data.img;

      template +='<div class="media-home">'; 
      template +='  <div class="media-left">'; 
      template +='    <a href="'+href+'"> ';
      template +='      <img class="media-object" data-src="holder.js/64x64" alt="64x64" src="'+img+'" data-holder-rendered="true" style="width: 119px; height: 59px;">'; 
      template +='    </a>'; 
      template +='  </div> ';
      template +='  <div class="media-body">'; 
      template +='    <h4 class="media-heading">';
      template +='      <a href="'+href+'">'+data.title+'</a>';
      template +='    </h4>'; 
      template +='    <div class="media-info">';
      template +=       data.date;
      template +='    </div>';
      template +='  </div> ';
      template +='</div>';
      template +='<hr class="blog-post-sep" style="margin-bottom: 0px;margin-top: 5px;">';
      return template;
      
    }
    var page = 1;

    $("#btn-load-more").click(function() {
      page = page+1;
      get_datas(page);
    })
});
</script>