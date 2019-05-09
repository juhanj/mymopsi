/*
ColumnType  | Max Value (signed/unsigned)

  TINYINT   |   127 / 255
 SMALLINT   |   32767 / 65535
MEDIUMINT   |   8388607 / 16777215
      INT   |   2147483647 / 4294967295
   BIGINT   |   9223372036854775807 / 18446744073709551615
*/

create database mymopsi
	character set utf8mb4
	collate utf8mb4_unicode_520_ci;

create table if not exists lang (
	lang     varchar(3)   not null comment 'Three character language code',      -- PK
	str_page varchar(25)  not null comment 'What page string is on',             -- PK
	str_type varchar(25)  not null comment 'Name of string, t.ex. "HTML_TITLE"', -- PK
	str      varchar(255) not null comment 'String/text to be printed on page',
	primary key ( lang, str_page, str_type )
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_520_ci;

create table if not exists user (
	id    int          not null auto_increment, -- PK
	email varchar(255) not null                 -- PK
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_520_ci
	auto_increment = 1;

create table if not exists collection (
	id          int not null auto_increment, -- PK
	owner_id    varchar(255) default null,   -- FK
	coll_name   varchar(50)  default null,
	description varchar(255) default null,
	public      boolean      default false comment 'Is the collection public, i.e. shown on front page',
	editable    boolean      default false comment 'Can anyone edit this, owner can always edit',
	added datetime,
	last_edited datetime,
	primary key ( id ),
	foreign key ( owner_id ) references user( id )
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_520_ci
	auto_increment = 1;

create table if not exists img (
	id        int            not null auto_increment,          -- PK
	collection_id int not null, -- FK
	name      varchar(255)   not null comment 'user editable', -- PK
	latitude  decimal(10, 7) not null comment 'in degrees',
	longitude decimal(10, 7) not null comment 'in degrees',
	date_added datetime,
	primary key ( id ),
	foreign key (collection_id) references collection ( id )
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_520_ci
	auto_increment = 1;
