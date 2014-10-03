
<div id="collaborate-notes-overlay">

<div class="container collaborate-notes" id="collaborate-notes">


	<div class="row">
		<div class="col-xs-12 status-section">
			

		<span class="note-saved save-status">All notes saved</span>
		<span class="note-saving save-status">Saving...</span>
		<button class="btn btn-sm">
			<span class="dashicons"><i class="fa fa-times"></i></span>
		</button>


		</div>
	</div><!-- /.row-->
	<div class="divider-border"></div>
	<div class="row">
		<div class="col-xs-12 text-center">

			<ul class="nav nav-pills nav-justified" role="tablist">
			  <li><a class="active">Active <span class="count-active"></span></a></li>
			  <li><a class="completed">Completed <span class="count-completed"></span></a></li>
			</ul>

		</div>
	</div><!-- /.row-->
	<div class="divider"></div>
	<div class="row">
		<div class="col-xs-12">			

			<div class="note-wrapper" id="new-note-wrapper">
				<div class="note" contenteditable="true" id="new-note" placeholder="Add note"></div>
				<div class="save-note first">
					<div class="save-note-inner text-left">
						<button class="btn btn-default show-tooltip reminder_button_new" data-toggle="tooltip" title="Remind me"><i class="fa fa-bell"></i></button>
						<button class="btn btn-default show-tooltip assigne_button_new" data-toggle="tooltip" title="Assigne users"><i class="fa fa-users"></i></button>
						<button class="btn btn-default pull-right" id="save-note">Done</button>
					</div>
				</div>

				<div class="reminder-box">
					<h3>Reminder:</h3>
					<div class="inside-box-border">
						<div class="unfinished-reminder-box">
							<input type="text" class="datepicker">
							<span style="padding: 0 10px;">at</span>
							<input class="timepicker" type="text" style="width: 80px;" value="09:00">
							<span class="reminder-notify">Reminder at <span class="datetime"></span></span>
							<button class="btn btn-default btn-sm reminder_add_button pull-right">Remind me</button>
						</div>
						<div class="finished-reminder-box">
							<span class="reminder-notify-finished"></span>
							<span class="pull-right" style="padding: 0 15px;"><a href="#" class="delete-reminder">X</a></span>
							<span class="pull-right"><a href="#" class="edit-reminder">edit</a></span>
						</div>
					</div>
				</div><!-- /.reminder-box-->

				<div class="selector-box">
					<h3>Assigne users:</h3>
					<div class="inside-box-border">
						<select data-placeholder="Choose Users..." multiple="" class="chosen-select"></select>
					</div>
				</div><!-- /.selector-box-->

			</div><!-- /.note-wrapper-->
			<div class="divider"></div>
		</div>
	</div><!-- /.row-->

	<div class="row">
		<div class="col-xs-12">

			<div id="notes-list"></div>

		</div>
	</div><!-- /.row-->





<script type="text/template" id="appView">
<div class="note-wrapper">
	<button class="btn new-notified" disabled="disabled" contenteditable="false"><i class="fa fa-check-circle-o"></i> Notified have been send to the assigned user(s)</button>
	<div class="alert update-notified">
	      <h4>Do you want to notify assigned users about this update?</h4>
	      <p>
	        <button type="button" class="btn btn-default btn-sm yes-notify-users">Yes, send notify</button>
	        <button type="button" class="btn btn-default btn-sm no-notify-users">No thanks</button>
	      </p>
	</div>
	<div class="note" contenteditable="true" id="<%= note_id %>">
		<%= description %>
	</div>

	<div class="save-note text-left">
	<div class="note-log text-right">
		<span class="log-message" style="font-size: 0.9em; font-style: italic;"><%= log_message %></span>
	</div>
	<div class="save-note-inner text-left">
		<button class="btn btn-default show-tooltip reminder_button" data-toggle="tooltip" title="Remind me"><i class="fa fa-bell"></i></button>
		<button class="btn btn-default show-tooltip assigne_button" title="Assigne users"<% if ( createdByYou == false || completed == true ) { %> disabled="disabled"<% } %>><i class="fa fa-users"></i></button>

		<% if ( createdByYou == false ) { %>
			<button class="btn btn-default show-tooltip completed" disabled="disabled" title="Make completed"><i class="fa fa-check"></i></button>
		<% } else { %>
			<% if ( completed == false ) { %>
				<button class="btn btn-default show-tooltip completed" title="Make completed"><i class="fa fa-check"></i></button>
			<% } else { %>
				<button class="btn btn-default show-tooltip completed" title="Undo completed"><i class="fa fa-check"></i></button>
			<% } %>
		<% } %>

		<button class="btn btn-default show-tooltip delete" title="Delete note"<% if ( createdByYou == false ) { %> disabled="disabled"<% } %>><i class="fa fa-trash-o"></i></button>

		<% if ( createdByYou == false ) { %>
			<button class="btn" disabled="disabled">
				<span class="" style="font-size: 0.9em;">Created by <%= display_name %></span>
			</button>
		<% } %>
		<button class="btn btn-default pull-right save">Done</button>

	</div><!-- /.save-note-inner-->
	</div><!-- /.save-note-->
	<input type="hidden" class="note_id" value="<%= note_id %>" />
	<input type="hidden" class="assigned_to" value="<%= assigned_to %>" />

	<div class="reminder-box">
		<h3>Reminder:</h3>
		<div class="inside-box-border">
			<div class="unfinished-reminder-box">
				<input type="text" class="datepicker">
				<span style="padding: 0 10px;">at</span>
				<input class="timepicker" type="text" style="width: 80px;">
				<span class="reminder-notify">Reminder at <span class="datetime"></span></span>
				<button class="btn btn-default btn-sm reminder_submit_button pull-right">Remind me</button>
			</div>
			<div class="finished-reminder-box">
				<span class="reminder-notify-finished"></span>
				<span class="pull-right" style="padding: 0 15px;"><a href="#" class="delete-reminder">X</a></span>
				<span class="pull-right"><a href="#" class="edit-reminder">edit</a></span>
			</div>
		</div>
	</div><!-- /.reminder-box-->

	<div class="selector-box">
		<h3>Assigne users:</h3>
		<div class="inside-box-border">
			<select data-placeholder="Choose Users..." multiple="" class="chosen-select-note" id="<%= note_id %>">
				<% _.each(jQuery.parseJSON(assigned_to), function(thing,key,list) { %>
					<% if (thing.selected) { %>
					<option value="<%= thing.user_id %>" selected><%= thing.display_name %> (<%= thing.user_email %>)</option>
					<% } else { %>
						<option value="<%= thing.user_id %>"><%= thing.display_name %> (<%= thing.user_email %>)</option>
					<% } %>
				<% }); %>
			</select>
		</div><!-- /.inside-box-border-->
	</div><!-- /.selector-box-->
	<div class="reminder-log"><a href="#" class="reminder-log-link"><i class="fa fa-clock-o"></i> Reminder at <span class="reminder-message"></span></a></div>
</div><!-- /.note-wrapper-->
<div class="divider"></div>
</script>

</div><!-- /.container-->

</div>
