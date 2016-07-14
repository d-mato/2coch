
$('.login-form').on('submit', function(e) {
  e.preventDefault();
  var $form = $(this);
  var $input = $('[name=pass]', $form);
  if ($input.val() == '')return false;
  var r = $.post('/admin/api.php?login', {pass: $input.val()});
  $('#loading').addClass('show');
  r.done(function() {
    $('#loading').removeClass('show');
    $form.remove()
    fetch_contacts();
  });
  r.fail(function() {
    $('#loading').removeClass('show');
    $input.val('');
    alert('Failed to login!');
  });
});

var fetch_contacts = function() {
  var r = $.get('/admin/api.php?contacts');
  r.done(function(json) {
    $('#loading').removeClass('show');
    var tbody = json.map(function(contact) {
      var row = '<tr>'
        +'<td>'+contact.date+'</td>'
        +'<td>'+contact.comment+'</td>'
        +'<td>'+contact.ip_addr+'</td>'
        +'</tr>';
      return row;
    }).join('');
    $('.contacts tbody').html(tbody);
    $('.contacts').addClass('loaded');
  });
  r.fail(function() {
    $('#loading').removeClass('show');
    alert('Failed to load');
  });
}
