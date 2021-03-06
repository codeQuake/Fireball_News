/**
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */

if (!Fireball) var Fireball = {};

Fireball.News = {};

Fireball.News.LabelSelection = Class.extend({
	_labelGroupIDsByCategory: {},

	_categoryIDsBylabelGroup: {},

	init: function (labelGroupIDsByCategory) {
		this._labelGroupIDsByCategory = labelGroupIDsByCategory;
		var groupID, self = this;

		$.each( labelGroupIDsByCategory, function( categoryID, groupIDs ) {
			$.each( groupIDs, function( key, groupID ) {
				if (self._categoryIDsBylabelGroup[groupID] == undefined) {
					self._categoryIDsBylabelGroup[groupID] = {};
				}
				if (self._categoryIDsBylabelGroup[groupID][categoryID] == undefined) {
					self._categoryIDsBylabelGroup[groupID][categoryID] = true;
				}
			});
		});

		$('#newsAddabelSelectionContainer dl').each(function (key, val) {
			groupID = $(val).find('> dd .labelList').data('objectID');

			for (var categoryID in self._categoryIDsBylabelGroup[groupID]) {
				if ($('.jsCategoryList input[value="'+categoryID+'"]').is(':checked')) {
					continue;
				}
				$(val).hide();
			}
		});

		$('.jsCategoryList input').each(function (key, val) {
			$(val).click($.proxy(self._categoryChange, self));
		})
	},

	_categoryChange: function (event) {
		var groupID, self = this;
		$('#newsAddabelSelectionContainer dl').each(function (key, val) {
			groupID = $(val).find('> dd .labelList').data('objectID');

			for (var categoryID in self._categoryIDsBylabelGroup[groupID]) {
				if ($('.jsCategoryList input[value="'+categoryID+'"]').is(':checked')) {
					$(val).show();
					continue;
				}
				$(val).hide();
			}
		});

	}
});

Fireball.News.MarkAllAsRead = Class.extend({
	_proxy: null,

	init: function () {
		// initialize proxy
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		//add clickhandler
		$('.markAllAsReadButton').click($.proxy(this._click, this));
	},

	_click: function () {
		this._proxy.setOption('data', {
			actionName: 'markAllAsRead',
			className: 'cms\\data\\news\\NewsAction'
		});

		this._proxy.sendRequest();
	},

	_success: function () {
		// hide unread messages
		$('#mainMenu').find('.active .badge').hide();
		$('.newMessageBadge').hide();
	}
});

Fireball.News.Preview = WCF.Popover.extend({
	/**
	 * action proxy
	 * @var WCF.Action.Proxy
	 */
	_proxy: null,

	/**
	 * list of links
	 * @var object
	 */
	_newss: {},

	/**
	 * @see WCF.Popover.init()
	 */
	init: function () {
		this._super('.newsLink');

		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false
		});
		WCF.DOMNodeInsertedHandler.addCallback('Fireball.News.Preview', $.proxy(this._initContainers, this));
	},

	/**
	 * @see WCF.Popover._loadContent()
	 */
	_loadContent: function () {
		var $news = $('#' + this._activeElementID);

		this._proxy.setOption('data', {
			actionName: 'getNewsPreview',
			className: 'cms\\data\\news\\NewsAction',
			objectIDs: [$news.data('newsID')]
		});

		var $elementID = this._activeElementID;
		var self = this;
		this._proxy.setOption('success', function (data, textStatus, jqXHR) {
			self._insertContent($elementID, data.returnValues.template, true);
		});
		this._proxy.sendRequest();
	}
});

Fireball.News.Like = WCF.Like.extend({

	_getContainers: function () {
		return $('article.message');
	},

	_getObjectID: function (containerID) {
		return this._containers[containerID].data('newsID');
	},

	_buildWidget: function (containerID, likeButton, dislikeButton, badge, summary) {
		var $widgetContainer = this._getWidgetContainer(containerID);
		if (this._canLike) {
			var $smallButtons = this._containers[containerID].find('.smallButtons');
			likeButton.insertBefore($smallButtons.find('.toTopLink'));
			dislikeButton.insertBefore($smallButtons.find('.toTopLink'));
			dislikeButton.find('a').addClass('button');
			likeButton.find('a').addClass('button');
		}

		if (summary) {
			summary.appendTo(this._containers[containerID].find('.messageBody > .messageFooter'));
			summary.addClass('messageFooterNote');
		}
		$widgetContainer.find('.permalink').after(badge);
	},


	_getWidgetContainer: function (containerID) {
		return this._containers[containerID].find('.messageHeader');
	},

	_addWidget: function (containerID, widget) {
	},

	_setActiveState: function (likeButton, dislikeButton, likeStatus) {
		likeButton = likeButton.find('.button').removeClass('active');
		dislikeButton = dislikeButton.find('.button').removeClass('active');

		if (likeStatus == 1) {
			likeButton.addClass('active');
		}
		else if (likeStatus == -1) {
			dislikeButton.addClass('active');
		}
	}
});
