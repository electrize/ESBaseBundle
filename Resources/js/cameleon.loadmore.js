window.Cameleon = window.Cameleon || {};

Cameleon.loadmore = {
	initAll: function () {
		function initAll(context) {
			$(context).find('[data-loadmore-area]').each(function () {
				Cameleon.loadmore.setup($(this), {
					reverse: $(this).attr('data-loadmore-area') === 'reverse'
				});
			});
		}

		$(window).on('shown.bs.modal', function (e) {
			initAll(e.target);
		});

		initAll(document);
	},
	setup: function ($container, options) {
		var options = $.extend({
			loadingText: "Loading ...",
			reverse: false,
			loadingClass: 'loading'
		}, options);

		$container.on('click', 'a.loadmore-btn', function (e) {
			e.preventDefault();
			var $a = $(this), $c = $a.closest('.loadmore-action');
			if ($a.hasClass(options.loadingClass)) {
				return;
			}
			$a.text(options.loadingText).addClass(options.loadingClass);

			Cameleon.request.get($a.attr('href'), null, function (resp) {

				var $target = $(resp);

				if (options.reverse) {
					$container.prepend($target);
				} else {
					$container.append($target);
				}
				$c.remove();
				$(document).trigger({
					type: 'loadmore-loaded',
					$target: $target
				});
			})
		});
	}
};

(function ($) {
	$.fn.cameleonLoadmore = function (options) {
		return this.each(function () {
			Cameleon.loadmore.setup($(this), options);
		});
	}
})(jQuery);
