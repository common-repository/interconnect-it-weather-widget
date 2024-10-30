jQuery( document ).ready( function( $ ) {
    $( '.color-picker' ).iris( );
    $( document ).on( 'widget-updated widget-added', function( e, $widget ) {
        $widget.find( '.color-picker' ).iris( );
    });
});