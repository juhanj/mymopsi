/*
ColumnType  | Max Value (signed/unsigned)

  TINYINT   |   127 / 255
 SMALLINT   |   32767 / 65535
MEDIUMINT   |   8388607 / 16777215
      INT   |   2147483647 / 4294967295
   BIGINT   |   9223372036854775807 / 18446744073709551615
*/

create table if not exists mymopsi_user (
	id         int         not null auto_increment,                                         -- PK
	random_uid varchar(20) not null comment 'public facing string for URLs and such',       -- UK
	username   varchar(50) comment 'Username for logging in. NULL if 3rd party login used', -- UK
	password   varchar(190) comment 'Hashed & salted. NULL if 3rd party login used.',
	type       tinyint     not null default 2 comment 'Password type. NULL:Not processed, 1:old, 2:new.',
	email      varchar(190),                                                                -- UK
	admin      boolean default false,
	primary key (id),
	unique email (email),                                                                   -- NULL values ignored in MySQL for unique constraint
	unique username (username),
	unique random_uid (random_uid)
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_ci
	auto_increment = 1;

create table if not exists mymopsi_user_third_party_link (
	user_id   int not null, -- PK
	mopsi_id  int,
	google_id varchar(190),
	primary key (user_id),
	constraint fk_3rdpartylink_user foreign key (user_id) references mymopsi_user (id)
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_ci;

create table if not exists mymopsi_collection (
	id          int                      not null auto_increment,                                   -- PK
	owner_id    int                      not null,                                                  -- PK FK
	random_uid  varchar(20)              not null comment 'public facing string for URLs and such', -- UK
	name        varchar(50),
	description text default null,                                                                  -- max ~65k characters
	public      boolean default false comment 'Is the collection public, i.e. shown on front page',
	editable    boolean default false comment 'Can anyone edit this, owner/admin can always edit',
	date_added  timestamp default now( ) not null,
	primary key (id),
	unique random_uid (random_uid),
	constraint fk_collection_user foreign key (owner_id) references mymopsi_user (id)
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_ci
	auto_increment = 1;

create table if not exists mymopsi_img (
	id            int                      not null auto_increment,                                               -- PK
	collection_id int                      not null,                                                              -- FK UK no_duplicates
	random_uid    varchar(20)              not null comment 'public facing string for URLs and such',             -- UK random_uid
	hash          char(40)                 not null comment 'SHA1 hash for comparing files (prevent duplicates)', -- UK no_dupl
	name          varchar(190)             not null comment 'user editable',
	original_name varchar(190)             not null comment 'for posterity',
	description   text default null,                                                                              -- max ~65k characters
	filepath      varchar(190) comment 'full real path with file extension',
	mediatype     varchar(50)              not null comment 'File media (or MIME) type',
	size          int                      not null comment 'in bytes',                                           -- UK no_duplicates
	latitude      float(10, 6)             null default null comment 'in degrees',
	longitude     float(10, 6)             null default null comment 'in degrees',
	date_created  timestamp                null default null comment 'file creation time (i.e. when was time taken)',
	date_added    timestamp default now( ) not null comment 'when added to database',
	primary key (id),
	unique random_uid (random_uid),
	unique no_duplicates (collection_id, hash, size),
	constraint fk_img_collection foreign key (collection_id) references mymopsi_collection (id)
)
	default charset = utf8mb4
	collate = utf8mb4_unicode_ci
	auto_increment = 1;