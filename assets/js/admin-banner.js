( function ( $ ) {
    $( document ).on( 'widget-added', function ( $event, $control ) {
        $control
            .find("input[id^=widget-listar_api_banner]")
            .filter(function () {
                return this.id.split('-').pop() === 'type' && this.type === 'radio';
            }).on('click', function() {
                if(this.value === 'admob') {
                    $(this).closest('div.widget').first()
                        .find("p[id^=widget-listar_api_banner]")
                        .filter(function () {
                            console.log(this.id.split('-').pop());
                            return this.id.split('-').pop() === 'admob_code';
                        }).removeClass('listar-hidden')
                    
                    $(this).closest('div.widget').first()
                        .find("p[id^=widget-listar_api_banner]")
                        .filter(function () {
                            console.log(this.id.split('-').pop());
                            return this.id.split('-').pop() === 'banner_elm';
                        }).addClass('listar-hidden')    
                } else {
                    $(this).closest('div.widget').first()
                        .find("p[id^=widget-listar_api_banner]")
                        .filter(function () {
                            return this.id.split('-').pop() === 'admob_code';
                        }).addClass('listar-hidden')
                    
                    $(this).closest('div.widget').first()
                        .find("p[id^=widget-listar_api_banner]")
                        .filter(function () {
                            console.log(this.id.split('-').pop());
                            return this.id.split('-').pop() === 'banner_elm';
                        }).removeClass('listar-hidden')
                }
            })
    });
} )( jQuery );