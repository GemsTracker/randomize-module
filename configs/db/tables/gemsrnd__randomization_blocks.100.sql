
CREATE TABLE if not exists gemsrnd__randomization_blocks (

        grb_block_id            varchar(30) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' not null,

        grb_condition           bigint unsigned null references gems__conditions (gcon_id),

        grb_value_id            varchar(30) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' not null references gemsrnd__randomization_values (grv_value_id),

        grb_study_id            bigint unsigned not null references gemsrnd__randomization_studies (grs_study_id),
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

        PRIMARY KEY (grb_block_id),
        UNIQUE (grb_study_id, grb_value_order),
        INDEX (grb_condition),
        INDEX (grb_value_id)
    )
    ENGINE = InnoDB
    CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';
