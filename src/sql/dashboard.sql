delimiter;

insert
    ignore into dashboard_available_types (xtype, classname, description)
values
    (
        'tualodashboard_msmail_state',
        'Tualo.MicrosoftMail.lazy.dashboard.State',
        ''
    );
insert
    ignore into dashboard (id, title, dashboard_type, position, configuration)
values
    (
        'msmail-8ece-11ee-aac7-ac1f6bd9bb0c',
        'Status',
        'tualodashboard_msmail_state',
        10,
        '{}'
    );