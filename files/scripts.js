(function($)
{
  $(document).ready(function()
  {
    $('#rex-output').on('click', '.nestedsets-preparation a', function(e)
    {
      e.preventDefault();
      
      if($(this).hasClass('deactivate') && !confirm($(this).attr('data-confirm')))
        return false;
      
      var url = $(this).attr('href');
      $('.nestedsets-preparation').addClass('loading');
      $.get(url, function()
      {
        $('#rex-output').load(url + ' #rex-output > *', function()
        {
          $('.nestedsets-preparation').removeClass('loading');
        });
      });
      
      return false;
    });
  });
}(jQuery));