$(document).ready(function(){
  $('.combobox').combobox();
  
  //more link
  $('.lead').readmore({
    moreLink: '<p class="lead"><a href="#">More&hellip;</a></p>',
    lessLink: '<p class="lead"><a href="#">Less&hellip;</a></p>'
  });
  
  //REASSIGN REQUEST
  $('#reassign').popover({ 
      html : true,
      title: function() {
        return $("#reassign-head").html();
      },
      content: function() {
        return $("#reassign-content").html();
      },
      placement: 'left'
  });
  $('#reassign').on('shown.bs.popover', function () {
    $('.close').on('click',function(){
      $('#reassign').popover('hide');
    });
  })
  
  //ADD HELPER
  $('#addHelper').popover({ 
      html : true,
      title: function() {
        return $("#addhelper-head").html();
      },
      content: function() {
        return $("#addhelper-content").html();
      },
      placement: 'left'
  });
  $('#addHelper').on('shown.bs.popover', function () {
    $('.close').on('click',function(){
      $('#addHelper').popover('hide');
    });
  })
  
  //REMOVE HELPER
  $("[id^=removeHelper]").popover({ 
      html : true,
      title: function() {
        var num = this.id.slice(12);
        return $("#removehelper-head"+num).html();
      },
      content: function() {
        var num = this.id.slice(12);
        return $("#addhelper-content"+num).html();
      },
      placement: 'left'
  });
  $("[id^=removeHelper]").on('shown.bs.popover', function () {
    var num = this.id.slice(12);
    $('.close').on('click',function(){
      $('#removeHelper'+num).popover('hide');
    });
  })
  
  //History popover
  $('#historyPopover').popover({ 
      html : true,
      title: function() {
        return $("#history-head").html();
      },
      content: function() {
        return $("#history-content").html();
      },
      placement: 'bottom'
  });
  $('#historyPopover').on('shown.bs.popover', function () {
    $('.close').on('click',function(){
      $('#historyPopover').popover('hide');
    });
  })
});