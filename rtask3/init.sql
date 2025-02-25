create table Application(
	id int unsigned not null auto_increment,
	fio varchar(256) not null default '',
	phone varchar(32) not null default '000',
	email varchar(64) not null default '',
	bdate varchar(16) not null default '',
	gender varchar(10) not null default 'Паркет',
	message varchar(1024) not null default '',
	primary key (id)
);

create table Language(
	id int unsigned not null auto_increment,
	name varchar(32) not null default '',
	primary key (id)
);

create table AppLang(
	app_id int unsigned not null,
	lang_id int unsigned not null,
	primary key (app_id, lang_id)
);

insert into Language (name) values ('C');
insert into Language (name) values ('C++');
insert into Language (name) values ('Lua');
insert into Language (name) values ('Python');
insert into Language (name) values ('JavaScript');
insert into Language (name) values ('PHP');
insert into Language (name) values ('Java');
insert into Language (name) values ('Paskal');
insert into Language (name) values ('Haskel');
insert into Language (name) values ('Rust');
insert into Language (name) values ('Clojure');
insert into Language (name) values ('Prolog');
insert into Language (name) values ('Scala');
