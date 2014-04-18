   $(function() {
      $( document ).tooltip({
      items: "img, [images], [title]",
      content: function() {
        var element = $( this );
        if ( element.is( "[images]" ) ) {
          var FicImage =element.attr("images")
           return "<img  src='"+FicImage+"'>";
        }
        if ( element.is( "[title]" ) ) {
          return element.attr( "title" );
        }
        if ( element.is( "img" ) ) {
          return element.attr( "alt" );
        }
      }
    });
  });
