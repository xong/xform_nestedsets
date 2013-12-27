(function($)
{
  $(document).ready(function()
  {
    $('#rex-output').on('click', '.nestedsets-preparation a', function(e)
    {
      e.preventDefault();
      
      if($(this).hasClass('deactivate') && !confirm($(this).attr('data-confirm')))
        return false;

      $('.nestedsets-preparation').addClass('loading')
      $('#rex-output').load($(this).attr('href') + ' #rex-output > *', function()
      {
        $('.nestedsets-preparation').removeClass('loading');
      });
      
      return false;
    });
  });
}(jQuery));