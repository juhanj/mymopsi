create table if not exists `Point` (
	`id`                bigint(11)                                        not null auto_increment,
	`name`              varchar(20)                                       not null,
	`type`              int(11)                                           not null,
	`collection_id`     bigint(20)                                        not null,
	`description`       varchar(160)                                      not null,
	`street_name`       varchar(40) default null,
	`street_number`     int(11) default null,
	`commune`           varchar(20) default null,
	`latitude`          double                                            not null,
	`longitude`         double                                            not null,
	`timestamp`         bigint(20)                                        not null,
	`date`              date                                              not null,
	`coordinate_source` enum ('unknown','fixed','gps','cell','corrected') not null default 'unknown',
	`location`          point                                             not null,
	`servertimestamp`   timestamp                                         not null default CURRENT_TIMESTAMP,
	`mgrs`              varchar(50) default null,
	primary key (`id`)
) default charset = utf8mb4
  collate = utf8mb4_unicode_ci
  auto_increment = 1;

create table if not exists `Photo` (
	`id`         bigint(11)   not null auto_increment,
	`point_id`   bigint(11)   not null,
	`photo_id`   varchar(128) not null,
	`service_id` bigint(20)   not null,
	`direction`  double       not null,
	`project`    varchar(30)  not null,
	`userid`     varchar(30) default null,
	`phone`      varchar(50)  not null,
	`format`     varchar(30)  not null,
	`software`   varchar(50)  not null,
	primary key (`id`)
) default charset = utf8mb4
  collate = utf8mb4_unicode_ci
  auto_increment = 1;