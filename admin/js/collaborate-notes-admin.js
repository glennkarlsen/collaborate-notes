window.CollaborateNotes = {
	Models: {},
	Collections: {},
	Views: {},
	Routers: {},
	isRendered: false,
	currentFilter: '',
	selectedNoteId: 'new-note'
};


jQuery(function($) {

CollaborateNotes.Routers.CollaborateNotes = Backbone.Router.extend({
	routes: {
		'collaborate-notes': 'startCollaborateNotes'
	},

	startCollaborateNotes: function() {
		$(document.body).css('overflow', 'hidden');
		$("#collaborate-notes-overlay").appendTo("#wpwrap #wpcontent #wpbody");
		$('#collaborate-notes-overlay').show().css('display', 'block');

		if (CollaborateNotes.isRendered) {
			$('.collaborate-notes').show().css('display', 'block');
		} else {

			var data = {
				'action': 'get_all_notes'
			};

			$.get(ajaxurl, data, function(response) {
				var notesCollection = new CollaborateNotes.Collections.Notes($.parseJSON(response));
				var app = new CollaborateNotes.Views.CollaborateNotesView({ collection: notesCollection });
			});
		}
	}
});


CollaborateNotes.Models.Note = Backbone.Model.extend({
	defaults: {
		note_id: 0,
		display_name: '',
		title: '',
		description: '',
		completed: false,
		assigned_to: '',
		createdByYou: true,
		notified: '',
		log_message: '',
		reminder: false,
		reminder_data: ''
	}
});

CollaborateNotes.Collections.Notes = Backbone.Collection.extend({
	model: CollaborateNotes.Models.Note,

	comparator: function(model) {
		return -model.get('note_id');
	}
});

CollaborateNotes.Views.CollaborateNotesView = Backbone.View.extend({
	el: '.collaborate-notes',

	events: {
		'click #save-note': 'newNote',
		'click #new-note': 'displayMenu',
		'keypress #new-note': 'displayMenu',
		'click .status-section button': 'shutDownCollaborateNotes',
		'click .assigne_button_new': 'toggleAssigneSelector',
		'click .reminder_button_new': 'toggleReminderBox',
		'click .reminder_add_button': 'addReminder',
		'click a.delete-reminder': 'deleteReminder',
		'click a.edit-reminder': 'editReminder',
		'click a.completed': 'setCurrentFilterCompleted',
		'click a.active': 'setCurrentFilterActive',
		'mouseenter .note-wrapper': 'setHover',
		'mouseleave .note-wrapper': 'unsetHover'
	},

	initialize: function() {
		CollaborateNotes.currentFilter = 'active';
		CollaborateNotes.isRendered = false;
		var mouseIsInside = false;
		var activeElement;
		var that = this;
		this.useReminder = false;


		this.listenTo(this.collection, 'add', this.addOne);
		this.listenTo(this.collection, 'all', this.updateCount);
		this.listenTo(this.collection, 'change:completed', this.setCurrentFilter);

		this.updateCount();
		this.render();

		_.bindAll( this, "onMouseUp", "closeIfEsc" );
		$('body').bind('mouseup', this.onMouseUp);
		$(window).bind('keydown', this.closeIfEsc);
		this.$el.hide().fadeIn("fast").css('display', 'block');
		this.$el.find('#new-note').focus();
		this.$el.find('.nav-justified .active').parent().addClass('active');

		this.$el.find('#new-note-wrapper .datepicker').val(moment().add(1, 'days').format("DD/MM/YYYY"));

		this.$el.find('#new-note-wrapper .datepicker').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true,
			todayHighlight: true,
			startDate: moment().format("DD/MM/YYYY")
		}).on('changeDate', function(e) {
			that.validateSelectedTime();
			var date = e.date;
			var reminderValue = moment(date).format("DD MMM") + " " + that.$el.find('#new-note-wrapper .timepicker').val();
			//that.$el.find('.reminder-notify .datetime').text(reminderValue);
			//that.$el.find('.reminder-notify').show();
		});

		this.$el.find('#new-note-wrapper .timepicker').timepicker({
			'timeFormat': 'H:i'
		});
		this.$el.find('#new-note-wrapper .timepicker').on('changeTime', function() {
			that.validateSelectedTime();
		});

		var selector = new CollaborateNotes.Views.SelectorView();

	},

	render: function() {
		if (CollaborateNotes.currentFilter === 'completed') {
			var active = this.collection.where({completed: true});
			//this.collection.each(this.addOne, this);
			_.each(active, this.addOne);
		} else {
			this.active = this.collection.where({completed: false});
			//this.collection.each(this.addOne, this);
			_.each(this.active, this.addOne);
		}
		CollaborateNotes.isRendered = true;

		return this;
	},

	validateSelectedTime: function() {
		var date = this.$el.find('#new-note-wrapper .datepicker').val();
		var time = this.$el.find('#new-note-wrapper .timepicker').val();

		var datetime = date + " " + time;


		if (moment(datetime, 'DD-MM-YYYY HH:mm').isBefore(moment()) || !moment(datetime, 'DD/MM/YYYY HH:mm', true).isValid()) {
			this.$el.find('#new-note-wrapper .timepicker').addClass('invalidBorder');
			this.useReminder = false;

			return false;
		} else {
			this.$el.find('#new-note-wrapper .timepicker').removeClass('invalidBorder');
			this.useReminder = true;

			return true;
		}

	},

	addReminder: function() {

		if (this.validateSelectedTime()) {
			var date = this.$el.find('#new-note-wrapper .datepicker').val();
			var time = this.$el.find('#new-note-wrapper .timepicker').val();

			var reminderValue = moment(date, "DD-MM-YYYY").format("DD MMM") + " " + time;
			// var reminderNotify = this.$el.find('.reminder-notify').hide();
			this.$el.find('#new-note-wrapper .reminder-notify-finished').text(reminderValue);
			this.$el.find('#new-note-wrapper .unfinished-reminder-box').hide();
			this.$el.find('#new-note-wrapper .finished-reminder-box').show();

			this.useReminder = true;
		} else {
			this.useReminder = false;

			return;
		}

	},

	deleteReminder: function(e) {
		e.preventDefault();
		that = this;
		//this.useReminder = false;
		this.$el.find('#new-note-wrapper .reminder-box').fadeOut('fast', function(e) {

			that.$el.find('#new-note-wrapper .timepicker').val('09:00');
			that.$el.find('#new-note-wrapper .datepicker').val(moment().add(1, 'days').format("DD/MM/YYYY"));
			that.$el.find('#new-note-wrapper .unfinished-reminder-box').show();
			that.$el.find('#new-note-wrapper .finished-reminder-box').hide();
		});
	},

	editReminder: function(e) {
		e.preventDefault();
		this.$el.find('#new-note-wrapper .unfinished-reminder-box').show();
		this.$el.find('#new-note-wrapper .finished-reminder-box').hide();
	},

	setCurrentFilter: function() {
		if (CollaborateNotes.currentFilter == 'completed') {
			this.setCurrentFilterCompleted();
		} else {
			this.setCurrentFilterActive();
		}
	},

	setCurrentFilterActive: function(e) {
		if(!typeof e === "undefined") {
			e.preventDefault();
		}

		this.$el.find('.nav-justified .completed').parent().removeClass('active');
		this.$el.find('.nav-justified .active').parent().addClass('active');
		//this.$el.find('ul li').addClass('active');

		CollaborateNotes.currentFilter = 'active';
		CollaborateNotes.isRendered = false;
		this.$el.find('#notes-list').html('');

		this.render();
	},

	setCurrentFilterCompleted: function(e) {
		if(!typeof e === "undefined") {
			e.preventDefault();
		}

		this.$el.find('.nav-justified .active').parent().removeClass('active');
		this.$el.find('.nav-justified .completed').parent().addClass('active');

		CollaborateNotes.currentFilter = 'completed';
		CollaborateNotes.isRendered = false;
		this.$el.find('#notes-list').html('');

		this.render();
	},

	updateCount: function() {
		var active = this.collection.where({completed: false});
		var completed = this.collection.where({completed: true});
		$('#count-notes').text(active.length);

		this.$el.find('.nav span.count-active').text("(" + active.length + ")");
		this.$el.find('.nav span.count-completed').text("(" + completed.length + ")");
	},

	addOne: function( note ) {
		var view = new CollaborateNotes.Views.Note({ model: note });

		if (CollaborateNotes.isRendered) {
			$('#notes-list').prepend( view.render().el );
		} else {
			$('#notes-list').append( view.render().el );
		}

		$('.chosen-select-note').chosen('destroy');
		$('.chosen-select-note').chosen({ width: '100%' });

		$.each($('.show-tooltip'), function(i) {
			$(this).tooltip();
		});

	},

	newNote: function() {

		var that = this.collection;
		var self = this;
		var assignedUsers = JSON.stringify(this.$el.find("#new-note-wrapper .chosen-select").val());
		var noteDescription = $.trim(this.$el.find('#new-note').html());

		if ( ! noteDescription > 0 ) return;

		var selectedTime;
		var selectedDate;

		if (this.useReminder) {
			selectedDate = this.$el.find('#new-note-wrapper .datepicker').val();
			selectedTime = this.$el.find('#new-note-wrapper .timepicker').val();
		}

		var data = {
			'action': 'add_note',
			'description': this.$el.find('#new-note').html(),
			'assigned_to': assignedUsers,
			'useReminder': this.useReminder,
			'date': selectedDate,
			'time': selectedTime
		};

		$.post(ajaxurl, data, function(response) {

			var newAttributes = function() {
				return {
					description: $.parseJSON(response).description,
					note_id: $.parseJSON(response).note_id,
					assigned_to: JSON.stringify($.parseJSON(response).assigned_to),
					log_message: $.parseJSON(response).log_message,
					completed: false,
					reminder: $.parseJSON(response).reminder,
					reminder_data: $.parseJSON(response).reminder_data
				};
			};

			that.add(newAttributes());

			var assignedUsersEmpty = $.parseJSON(response).assigned_to[0];

			if (CollaborateNotes.currentFilter === 'completed') {
				self.setCurrentFilterActive();
			}

			if (!assignedUsersEmpty) {
				var note = $('.new-notified:first');

				$(note).fadeIn().delay(4000).queue(function(n) {
					$(this).fadeOut(); n();
				});
			}

		});

		this.emptyField();
		this.$el.find(".save-note").removeClass('editing');
		this.$el.find('#new-note-wrapper .chosen-select').val('').trigger('chosen:updated');


		this.$el.find(".selector-box").hide();
		this.$el.find("#new-note-wrapper .selector-box").slideUp();
		this.$el.find('#new-note').focus();

		this.useReminder = false;
		this.$el.find('#new-note-wrapper .timepicker').val('09:00');
		this.$el.find('#new-note-wrapper .datepicker').val(moment().add(1, 'days').format("DD/MM/YYYY"));
		this.$el.find('#new-note-wrapper .timepicker').removeClass('invalidBorder');
		this.$el.find('#new-note-wrapper .unfinished-reminder-box').show();
		this.$el.find('#new-note-wrapper .finished-reminder-box').hide();
		this.$el.find('#new-note-wrapper .reminder-box').hide();
	},

	displayMenu: function( event ) {
		if (CollaborateNotes.selectedNoteId != 'new-note') {

			$(".selector-box").hide();
			$(".selector-box").slideUp();

			$(".reminder-box").hide();
			$(".reminder-box").slideUp();

		}
		CollaborateNotes.selectedNoteId = 'new-note';
		$(".save-note").removeClass('editing');

		this.$el.find('.reminder-log').removeClass('reminder-active');

		var note = $(event.currentTarget);
		note.next().addClass('editing');
	},

	emptyField: function() {
		this.$el.find('#new-note').text("");
	},

	setHover: function(e) {
		this.mouseIsInside = true;
		this.activeElement = e.currentTarget;
	},

	unsetHover: function() {
		this.mouseIsInside = false;
	},

	onMouseUp: function(event) {
		$('body').removeClass('editing');

		var container = $(".datepicker");
		var container2 = $(".ui-timepicker-wrapper");

		// if (!container.is(event.target) // if the target of the click isn't the container...
		// && container.has(event.target).length === 0) // ... nor a descendant of the container
		// {
		// 	container.hide();
		// }

		if ( ! this.mouseIsInside && !container.is(event.target) && container.has(event.target).length === 0
			&& !container2.is(event.target) && container2.has(event.target).length === 0) {


			if (this.$el.find('#new-note').text().length > 0) {
				this.newNote();
			}

			$(".save-note").removeClass('editing');
			this.$el.find(".selector-box").hide();
			this.$el.find(".reminder-box").hide();
			this.$el.find(".selector-box").slideUp();
			this.$el.find(".reminder-box").slideUp();

			this.$el.find('.reminder-log').removeClass('reminder-active');

			this.$el.find('.has-reminder').each(function() {
				$(this).show();
			});

		}
	},

	shutDownCollaborateNotes: function() {
		window.history.back();
		this.$el.hide().css('display', 'none');
		$('#collaborate-notes-overlay').hide().css('display', 'none');
		$(document.body).css('overflow', 'auto');
	},

  closeIfEsc: function(key) {
    if (key.keyCode == 27) {
      this.shutDownCollaborateNotes();
    }
  },

	toggleAssigneSelector: function() {
		this.$el.find('#new-note-wrapper .reminder-box').slideUp();
		this.$el.find('#new-note-wrapper .selector-box').slideToggle();
		this.$el.find('#new-note-wrapper .chosen-select').chosen('destroy');
		this.$el.find('#new-note-wrapper .chosen-select').chosen({ width: '100%' });

		this.$el.find('#new-note-wrapper .chosen-select').on('chosen:hiding_dropdown', function(evt, params) {
			//this.$el.find('#new-note-wrapper .chosen-select').val('').trigger('chosen:updated');
			$('#new-note-wrapper .chosen-select').chosen('destroy');
			$('#new-note-wrapper .chosen-select').chosen({ width: '100%' });
		});
	},

	toggleReminderBox: function() {
		this.$el.find('#new-note-wrapper .selector-box').slideUp();
		this.$el.find('#new-note-wrapper .reminder-box').slideToggle();
	}
});









CollaborateNotes.Views.Note = Backbone.View.extend({

	template: _.template( $('#appView').html() ),

	events: {
		'click .delete': 'deleteNote',
		'click .note-wrapper': 'displayMenu',
		'click button.completed': 'toggleCompleted',
		'keydown .note': 'startTimer',
		'click .save': 'saveButton',
		'click .assigne_button': 'toggleAssigneSelector',
		'click .reminder-log-link': 'reminderLogLink',
		'click .reminder_button': 'toggleReminderBox',
		'click .reminder_submit_button': 'submitReminder',
		'click a.edit-reminder': 'editReminder',
		'click a.delete-reminder': 'deleteReminder',
		'click .yes-notify-users': 'yesNotifyUsers',
		'click .no-notify-users': 'noNotifyUsers',
	},

	initialize: function() {
		this.listenTo(this.model, 'destroy', this.remove);
		//this.listenTo(this.model, 'change', this.updateNote);
		//this.listenTo(this.model, 'visible', this.toggleVisible);

		var toggleAssigneSelector = false;
	},

	reminderLogLink: function(e) {
		e.preventDefault();
		this.displayMenu();
		this.toggleReminderBox();
	},

	render: function() {
		var that = this;

		this.$el.html( this.template( this.model.toJSON() ) );

		var date = moment(this.model.get('reminder_data'), "YYYY-MM-DD HH:mm:ss").format("DD/MM/YYYY");
		var time = moment(this.model.get('reminder_data'), "YYYY-MM-DD HH:mm:ss").format("HH:mm");
		var reminderValue = moment(this.model.get('reminder_data'), "YYYY-MM-DD HH:mm:ss").format("DD MMM") + " " + time;

		if (this.model.get('reminder')) {
			this.$el.find('.unfinished-reminder-box').hide();
			this.$el.find('.finished-reminder-box').show();

			this.$el.find('.reminder-log').addClass('has-reminder');
			this.$el.find('.reminder-log').show();
			this.$el.find('.reminder-message').text(reminderValue);

			this.$el.find('.reminder-notify-finished').text(reminderValue);
			this.$el.find('.datepicker').val(date);
			this.$el.find('.timepicker').val(time);

		} else {
			this.$el.find('.note-wrapper .datepicker').val(moment().add(1, 'days').format("DD/MM/YYYY"));
			this.$el.find('.note-wrapper .timepicker').val('09:00');
		}

		this.$el.find('.note-wrapper .datepicker').datepicker({
			format: "dd/mm/yyyy",
			autoclose: true,
			todayHighlight: true,
			startDate: moment().format("DD/MM/YYYY")
		}).on('changeDate', function(e) {
			that.validateSelectedTime();
		});

		this.$el.find('.note-wrapper .timepicker').timepicker({
			'timeFormat': 'H:i'
		});
		this.$el.find('.note-wrapper .timepicker').on('changeTime', function() {
			that.validateSelectedTime();
		});

		return this;
	},

	submitReminder: function() {

		if (this.validateSelectedTime()) {
			if (this.model.get('reminder') == true) {
				this.updateReminder();
			} else {
				this.addReminder();
			}

			this.$el.find('.note-wrapper .unfinished-reminder-box').hide();
			this.$el.find('.note-wrapper .finished-reminder-box').show();

			var date = this.$el.find('.note-wrapper .datepicker').val();
			var time = this.$el.find('.note-wrapper .timepicker').val();

			var reminderValue = moment(date, "DD-MM-YYYY").format("DD MMM") + " " + time;

			this.$el.find('.note-wrapper .reminder-notify-finished').text(reminderValue);
			this.$el.find('.note-wrapper .reminder-message').text(reminderValue);
		} else {
			return;
		}
	},

	updateReminder: function() {
		var that = this;

		var data = {
			'action': 'update_reminder',
			'note_id': this.$el.find('.note_id').val(),
			'date': this.$el.find('.datepicker').val(),
			'time': this.$el.find('.timepicker').val()
		};

		$.post(ajaxurl, data, function(response) {
			that.model.set('reminder', true);
		});
	},


	addReminder: function() {
		var that = this;

		var data = {
			'action': 'add_reminder',
			'note_id': this.$el.find('.note_id').val(),
			'date': this.$el.find('.datepicker').val(),
			'time': this.$el.find('.timepicker').val()
		};

		this.$el.find('.reminder-log').addClass('has-reminder');

		$.post(ajaxurl, data, function(response) {
			that.model.set('reminder', true);
		});
	},

	editReminder: function(e) {
		e.preventDefault();
		this.$el.find('.note-wrapper .unfinished-reminder-box').show();
		this.$el.find('.note-wrapper .finished-reminder-box').hide();
	},

	deleteReminder: function(e) {
		e.preventDefault();

		var that = this;

		var data = {
			'action': 'delete_reminder',
			'note_id': this.$el.find('.note_id').val(),
		};

		this.$el.find('.reminder-log').removeClass('has-reminder');

		$.post(ajaxurl, data, function(response) {
			that.model.set('reminder', false);

			that.$el.find('.note-wrapper .reminder-box').fadeOut('fast', function(e) {

				that.$el.find('.note-wrapper .timepicker').val('09:00');
				that.$el.find('.note-wrapper .datepicker').val(moment().add(1, 'days').format("DD/MM/YYYY"));
				that.$el.find('.note-wrapper .unfinished-reminder-box').show();
				that.$el.find('.note-wrapper .finished-reminder-box').hide();
			});

		});

	},

	validateSelectedTime: function() {
		var date = this.$el.find('.note-wrapper .datepicker').val();
		var time = this.$el.find('.note-wrapper .timepicker').val();

		var datetime = date + " " + time;

		if (moment(datetime, 'DD-MM-YYYY HH:mm').isBefore(moment()) || !moment(datetime, 'DD/MM/YYYY HH:mm', true).isValid()) {
			this.$el.find('.note-wrapper .timepicker').addClass('invalidBorder');
			this.useReminder = false;

			return false;
		} else {
			this.$el.find('.note-wrapper .timepicker').removeClass('invalidBorder');
			this.useReminder = true;

			return true;
		}

	},


	yesNotifyUsers: function() {
		var noteId = this.$el.find('input.note_id').val();
		that = this;

		var data = {
			'action': 'notify_users',
			'note_id': noteId
		};

		$.post(ajaxurl, data, function(response) {

			that.$el.find(".update-notified").hide();
			that.$el.find(".new-notified").fadeIn().delay(4000).queue(function(n) {
				$(this).fadeOut(); n();
			});
		});

	},

	noNotifyUsers: function() {
		this.$el.find(".update-notified").hide();
	},

	deleteNote: function() {
		var noteId = this.$el.find('input.note_id').val();

		var data = {
			'action': 'delete_note',
			'note_id': noteId
		};

		var that = this;

		$.post(ajaxurl, data, function(response) {
			if (response) {
				that.model.destroy();
			} else {
				// TAKE LOOK AT THIS
				alert("You have not created this note!");
			}
		});
	},

	displayMenu: function() {
		if (CollaborateNotes.selectedNoteId != this.model.get('note_id')) {

			$(".selector-box").hide();
			$(".selector-box").slideUp();

			$(".reminder-box").hide();
			$(".reminder-box").slideUp();

			$('.reminder-log').removeClass('reminder-active');

		}
		CollaborateNotes.selectedNoteId = this.model.get('note_id');
		$(".save-note").removeClass('editing');

		this.$el.find(".save-note").addClass('editing');
		this.$el.find('.reminder-log').addClass('reminder-active');
		this.$el.find('.reminder-log').addClass('reminder-active');
	},

	hideMenu: function() {
		this.$el.find('.save-note').removeClass('editing');
		this.$el.find('.selector-box').hide();
		this.$el.find('.reminder-box').hide();
	},

	updateNote: function() {
		var noteId = this.$el.find('.note_id').val();
		var noteDescription = this.$el.find('.note').html();
		var assignedTo = this.$el.find('.chosen-select-note').val();
		var that = this;

		var data = {
			'action': 'update_note',
			'note_id': noteId,
			'description': noteDescription,
			'assigned_to': JSON.stringify(assignedTo),
			'completed': this.model.get('completed')
		};

		$.post(ajaxurl, data, function(response) {

			that.model.set("log_message", $.parseJSON(response).log_message);
			that.model.set("description", noteDescription);
			that.model.set("reminder_data", $.parseJSON(response).reminder);

			that.$el.find(".log-message").text(that.model.get('log_message'));

			var notified = $.parseJSON(response).notified;

			if (assignedTo !== null && !notified) {
				that.$el.find(".update-notified").fadeIn("fast");
			}

			if (notified) {

				that.$el.find(".new-notified").fadeIn().delay(4000).queue(function(n) {
					$(this).fadeOut(); n();
				});
			}


			data = {
				'action': 'get_correct_userlist',
				'note_id': noteId
			};

			$.post(ajaxurl, data, function(response) {
				that.$el.find('.chosen-select-note').chosen('destroy');
				that.$el.find('.chosen-select-note').chosen({ width: '100%' });

				that.model.set("assigned_to", response);
			});

		});
	},

	startTimer: function(event) {
		this.displayMenu();
		var noteDescription = this.$el.find('.note').html();

		if ( ! noteDescription.length > 0 ) return;

		var notAllowedKeys = [9,16,18,20,27,91,93,37,38,39,40];
		if ($.inArray(event.keyCode, notAllowedKeys) > -1)	return;

		var that = this;

		$('.collaborate-notes .note-saved').hide();
		$('.collaborate-notes .note-saving').show();

		if (that.timer) {
			clearTimeout(that.timer);
		}

		that.timer = setTimeout(function() {
			that.updateNote();
			that.timer = null;

		$('.collaborate-notes .note-saved').show();
		$('.collaborate-notes .note-saving').hide();
		}, 500);
	},

	toggleCompleted: function() {
		var completed = this.model.get('completed');

		this.model.set({completed: !completed});
		this.updateNote();
	},

	saveButton: function() {
		this.updateNote();
		this.hideMenu();
	},

	toggleReminderBox: function() {
		var that = this;
		this.$el.find('.selector-box').slideUp();
		this.$el.find('.note-wrapper .reminder-box').slideToggle('fast', function(e) {

			if (that.$el.find('.note-wrapper .reminder-box').is(':hidden')) {
				if (that.$el.find('.has-reminder').length) {
					that.$el.find('.reminder-log').fadeIn("fast");
				}
			} else {
				that.$el.find('.reminder-log').fadeOut("fast");
			}
		});
	},

	toggleAssigneSelector: function() {
		var that = this;
		this.$el.find('.note-wrapper .reminder-box').slideUp();
		this.$el.find('.selector-box').slideToggle('fast');
		$('.chosen-select-note').chosen('destroy');
		$('.chosen-select-note').chosen({ width: '100%' });

		if (that.$el.find('.has-reminder').length) {
			that.$el.find('.reminder-log').fadeIn("fast");
		}

		this.$el.find('.chosen-select-note').on('chosen:hiding_dropdown', function(evt, params) {
			$('.chosen-select-note').chosen('destroy');
			$('.chosen-select-note').chosen({ width: '100%' });
		});
	}

});


CollaborateNotes.Views.SelectorView = Backbone.View.extend({

	initialize: function() {
		$('#new-note-wrapper .chosen-select').chosen();
		$('.chosen-select-note').chosen();

		var data = {
			'action': 'get_user_list',
		};

		$.get(ajaxurl, data, function(response) {

			$.each($.parseJSON(response), function(i, value) {
				$('#new-note-wrapper .chosen-select').append('<option value="' + value.user_id + '">' + value.display_name + ' ('+ value.email +')</option>');
			});
		});
	}

});

new CollaborateNotes.Routers.CollaborateNotes();
Backbone.history.start();

});
