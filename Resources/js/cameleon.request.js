window.Cameleon = window.Cameleon || {};

Cameleon.request = {
	config: {
		timeoutError: 'Server does not respond, please try again later',
		serverError: 'An error has occurred, please try again later',
		loginUrl: null,
		onForbidden: function (errorText, xhr) {
			if (Cameleon.request.config.loginUrl) {
				document.location.href = Cameleon.request.config.loginUrl + '?r=' + document.location.href;
			} else {
				alert(errorText);
			}
		},
		loggedIn: false
	},

	setConfig: function (options) {
		$.extend(Cameleon.request.config, options);
	},

	get: function (url, params, success, options) {
		return Cameleon.request.request('get', url, params, success, options);
	},

	post: function (url, data, success, options) {
		return Cameleon.request.request('post', url, data, success, options);
	},

	delete: function (url, data, success, options) {
		return Cameleon.request.request('delete', url, data, success, options);
	},

	request: function (method, url, data, success, options) {
		return Cameleon.request.ajax($.extend({
			url: url,
			type: method,
			data: data,
			success: success
		}, options));
	},

	ajax: function (options) {
		if (options.error) {
			options._error = options.error;
			delete options.error;
		}

		if (options.requiredLogin && !Cameleon.request.config.loggedIn) {
			Cameleon.request.config.onForbidden.call(this);
		}

		return $.ajax(
			$.extend({
				error: function (xhr, type, errorText) {
					var r = Cameleon.request,
						m = Cameleon.modal,
						s = xhr.status;
					if (options._error) {
						options._error.call(this, xhr, type, errorText);
					}
					if (s === 400) {
					} else if (s === 403) {
						Cameleon.request.config.onForbidden.call(this);
					} else if (type === 'error' && xhr.readyState > 0) {
						m.alert(r.config.serverError);
					} else if (type === 'timeout') {
						m.alert(r.config.timeoutError);
					}
				}
			}, options)
		);
	},

	alertError: function (error) {
		if (Cameleon.modal) {
			Cameleon.modal.alert('<p>' + (error || Cameleon.request.config.serverError) + '</p>');
		} else {
			alert(error || Cameleon.request.config.serverError);
		}
	}
}