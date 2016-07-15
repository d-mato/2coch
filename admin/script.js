var loading_on = function() {$('#loading').removeClass('hidden')};
var loading_off = function() {$('#loading').addClass('hidden')};

$('.login-form').on('submit', function(e) {
  e.preventDefault();
  var $form = $(this);
  var $input = $('[name=pass]', $form);
  if ($input.val() == '')return false;

  loading_on();
  var r = $.post('/admin/api.php?login', {pass: $input.val()});
  r.always(loading_off);
  r.done(function() {
    $form.remove()
    fetch_contacts();
  });
  r.fail(function() {
    $input.val('');
    alert('Failed to login!');
  });
});

var fetch_contacts = function() {
  var $contacts = $('.contacts').addClass('hidden');

  loading_on();
  var r = $.get('/admin/api.php?contacts');
  r.always(loading_off);
  r.done(function(json) {
    var tmpl = _.template('<tr><td><%- date %></td><td><%- comment %></td><td><%- ip_addr %></td></tr>');
    var tbody = json.map(function(contact) {
      return tmpl(contact).replace(/&lt;br&gt;/g, '<br>');
    }).join('');
    $('tbody', $contacts).html(tbody);
    $contacts.fadeIn().removeClass('hidden');
  });
  r.fail(function() {
    alert('Failed to load');
  });
};
