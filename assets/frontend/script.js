(function($) {
    var StoreFrontCommonConfig = {
        init: function() {
            this.bindEvents();
        },
        bindEvents: function() {
            this.handleDropdown(); //Handle
            this.closeDropdownOutside(); // Close dropdown when clicking outside
        },

        handleDropdown: function(){            
            $(document).on('click', '.msfc-dropdown-icon', function(){
                $('.msfc-dropdown-menu').hide();
                $(this).closest('.msfc-dropdown').find('.msfc-dropdown-menu').toggle();
            });
        },
        closeDropdownOutside: function() {
            $(document).on('click', function(event) {
                if (!$(event.target).closest('.msfc-dropdown').length) {
                    $('.msfc-dropdown-menu').hide();
                }
            });
        }
    }
    StoreFrontCommonConfig.init();
})(jQuery)