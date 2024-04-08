$(function(){
    
    $('input.total_payment_money').on('input', function(e){        
        $(this).val(formatCurrency(this));
    }).on('keypress',function(e){
        if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
    }).on('paste', function(e){    
        var cb = e.originalEvent.clipboardData || window.clipboardData;      
        if(!$.isNumeric(cb.getData('text'))) e.preventDefault();
    });


    $('input.customer_paid_string').on('input', function(e){        
        $(this).val(formatCurrency(this));
    }).on('keypress',function(e){
        if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
    });

});