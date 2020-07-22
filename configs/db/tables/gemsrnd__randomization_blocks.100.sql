
CREATE TABLE if not exists gemsrnd__randomization_blocks (

        grb_value_id            varchar(30) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' not null,

        grb_condition           bigint unsigned null references gems__conditions (gcon_id),

        grb_value               varchar(30) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' not null,

        grb_study_name          varchar(30) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' not null,
        grb_value_order         bigint not null default 10,

        grb_block_description   varchar(30) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' null,
        grb_block_info          varchar(50) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' null,

        grb_active              boolean not null default 1,
        grb_use_count           bigint not null default 0,
        grb_use_max             bigint not null default 1,

        grb_changed             timestamp not null default current_timestamp on update current_timestamp,
        grb_changed_by          bigint unsigned not null,
        grb_created             timestamp not null,
        grb_created_by          bigint unsigned not null,

        PRIMARY KEY (grb_value_id),
        UNIQUE (grb_study_name, grb_value_order),
        INDEX (grb_condition)
    )
    ENGINE = InnoDB
    CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';
