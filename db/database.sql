/*
ColumnType  | Max Value (signed/unsigned)

  TINYINT   |   127 / 255
 SMALLINT   |   32767 / 65535
MEDIUMINT   |   8388607 / 16777215
      INT   |   2147483647 / 4294967295
   BIGINT   |   9223372036854775807 / 18446744073709551615
*/

create table if not exists mymopsi_user (
	id         int          not null auto_increment, -- PK
	random_uid varchar(20)  not null comment 'public facing string for URLs and such',
	email      varchar(180) not null,                -- UK
	primary key ( id ),
	unique ( email ),
	unique ( random_uid )
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_520_ci
	auto_increment = 1;

create table if not exists mymopsi_collection (
	id          int                         not null auto_increment,                                   -- PK
	owner_id    int          default null,                                                             -- FK
	random_uid  varchar(20)                 not null comment 'public facing string for URLs and such', -- UK
	name        varchar(50)                 not null,
	description varchar(255) default null,
	public      boolean      default false comment 'Is the collection public, i.e. shown on front page',
	editable    boolean      default false comment 'Can anyone edit this, owner/admin can always edit',
	date_added  timestamp    default now( ) not null,
	last_edited timestamp,
	primary key ( id ),
	unique ( random_uid ),
	constraint fk_collection_user foreign key ( owner_id ) references mymopsi_user( id )
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_520_ci
	auto_increment = 1;

create table if not exists mymopsi_img (
	id            int            not null auto_increment,                                   -- PK
	collection_id int            not null,                                                  -- FK UK
	random_uid    varchar(20)    not null comment 'public facing string for URLs and such', -- UK
	hash          char(40)       not null comment 'SHA1 hash for comparing files (prevent duplicates)', -- UK
	name          varchar(180)   not null comment 'user editable',
	original_name varchar(180)   not null comment 'for posterity',
	extension     varchar(5)     not null comment 'file extension',
	mediatype     varchar(50)    not null comment 'File media (or MIME) type',
	size          int            not null comment 'in bytes (bits?)', -- UK
	latitude      decimal(10, 7) null default null comment 'in degrees',
	longitude     decimal(10, 7) null default null comment 'in degrees',
	date_created  timestamp      null default null comment 'file creation time (i.e. when was time taken)',
	date_added    timestamp           default now( ) not null comment 'when added to database',
	primary key ( id ),
	unique ( random_uid ),
	unique no_duplicates ( collection_id, hash, size ),
	constraint fk_img_collection foreign key ( collection_id ) references mymopsi_collection( id )
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_520_ci
	auto_increment = 1;
