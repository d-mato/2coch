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
    var id = $(this).text();
    var span = $('<span class="count">')
      .html('('+($('span.'+id).index($(this))+1)+'/'+id_list[id]+')')
      .insertAfter($(this));
    if(parseInt(id_list[id])>9){
      $(this).addClass('red');
    }else if(parseInt(id_list[id])>3){
      $(this).addClass('green');
    }
  })

	var comment_box = $('<div>').css({
		display:'none',
		position:'absolute',
		width:'700px',
		backgroundColor:'#ddd',
		border:'solid 1px #fff',
		padding:'5px',
	}).appendTo($('body'));

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
			display:'block',
		});
		e.stopPropagation();
	});

	$('body').click(function(){
		comment_box.hide();
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
