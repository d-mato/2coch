
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
    var tmpl = _.template('<tr><td><%- date %></td><td><%- comment %></td><td><%- ip_addr %></td></tr>');
    var tbody = json.map(function(contact) {
      return tmpl(contact).replace(/&lt;br&gt;/g, '<br>');
    }).join('');
    $('.contacts tbody').html(tbody);
    $('.contacts').addClass('loaded');
  });
  r.fail(function() {
    $('#loading').removeClass('show');
    alert('Failed to load');
  });
}
