/*
	Simple OpenID Plugin
	http://code.google.com/p/openid-selector/
	
	This code is licenced under the New BSD License.
*/

var providers_large = {
	yandex : {
		name : 'Яндекс',
		url : 'http://openid.yandex.ru'
	},
	google : {
        name : 'Google',
        url : 'https://www.google.com/accounts/o8/id'
    },
	mailru : {
        name : 'Mail.ru',
        label : 'Введите ваше имя пользователя на Mail.ru.',
        url : 'http://{username}.id.mail.ru/'
    },
	vkontakte : {
        name : 'ВКонтакте',
        url : 'http://vkontakte.ru/'
    },
	fb : {
        name : 'Facebook',
        url : 'http://facebook.com'
    },
	tw : {
        name : 'Twitter',
        url : 'http://twitter.com'
    },
};

var providers_small = {
	openid : {
		name : 'OpenID',
		label : 'Введите ваш OpenID.',
		url : null
	},
	livejournal : {
		name : 'Живой Журнал',
		label : 'Введите ваше имя в Живом Журнале.',
		url : 'http://{username}.livejournal.com/'
	},
	flickr : {
		name : 'Flickr',
		label : 'Введите ваше имя на Flickr.',
		url : 'http://flickr.com/{username}/'
	},
	wordpress : {
		name : 'Wordpress',
		label : 'Введите ваше имя на Wordpress.com.',
		url : 'http://{username}.wordpress.com/'
	},
	blogger : {
		name : 'Blogger',
		label : 'Ваш Blogger аккаунт.',
		url : 'http://{username}.blogspot.com/'
	},
	verisign : {
		name : 'Verisign',
		label : 'Ваше имя пользователя на Verisign.',
		url : 'http://{username}.pip.verisignlabs.com/'
	},
	google_profile : {
		name : 'Профиль Google',
		label : 'Введите ваше имя на Google Profile.',
		url : 'http://www.google.com/profiles/{username}'
	},
	yahoo : {
        name : 'Yahoo',
        url : 'http://me.yahoo.com/'
    },
	myopenid : {
        name : 'MyOpenID',
        label : 'Введите ваше имя пользователя на MyOpenID.',
        url : 'http://{username}.myopenid.com/'
    },
};

openid.lang = 'ru';
openid.demo_text = 'В демонстрационном режиме на клиенте. В действительности произошел бы сабмит следующего OpenID:';
openid.signin_text = 'Войти';
openid.image_title = 'войти c {provider}';
