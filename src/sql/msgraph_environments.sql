DELIMITER ;

create table if not exists msgraph_environments (
    id varchar(36) not null primary key,
    login varchar(255) not null,
    updated datetime,
    expires datetime,
    val json not null
);

create table if not exists msgraph_setup (
    id varchar(36) not null primary key,
    val varchar(255) not null
);
