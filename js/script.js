$(function(){
	/* id毎の書き込み数をカウント */
	var id_list = [];
	$('span.id').each(function(){
		var id = $(this).text();
		if(id in id_list){
			id_list[id]++;
		}else{
			id_list[id] = 1;
		}
	});
  $('span.id').each(function(){
    var $span = $(this);
    var id = $span.text();
    var total_count = parseInt(id_list[id]);
    $('<span class="count">')
      .html('('+($('span.'+id).index($span)+1)+'/'+total_count+')')
      .insertAfter($span);
    if(total_count > 9){
      $span.addClass('red');
    }else if(total_count > 3){
      $span.addClass('green');
    }
  })

  var comment_box = $('<div class="comment_box"/>').appendTo(document.body);
	$('span.id').click(function(e){
		var id = $(this).text();
		comment_box.html('');
		$('span.'+id).each(function(){
      comment_box.append($(this).parent().clone());
      comment_box.append($(this).parent().next().clone());
		});
		comment_box.css({
			top:e.pageY+'px',
			left:e.pageX+'px',
		}).addClass('show');
		e.stopPropagation();
	});

  $(document.body).click(function(){
    comment_box.removeClass('show');
  });

  //内部リンク
  (function() {
    var info_comment_box = $('dd p')[0];
    if (!info_comment_box) return;
    var comment = $(info_comment_box).html();
    var ids = comment.match(/sm\d+/g)
    if (ids === null) return;
    ids.forEach(function(id) {
      comment = comment.replace(id, '<a href="/read.php?v='+id+'">'+id+'</a>');
    });
    $(info_comment_box).html(comment);
  })();
});
