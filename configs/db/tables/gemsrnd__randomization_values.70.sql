

CREATE TABLE if not exists gemsrnd__randomization_values (
        grv_value_id            bigint unsigned not null auto_increment,
        
        grv_study_id            bigint unsigned not null  references gemsrnd__randomization_studies (grs_study_id),
        grv_value               varchar(30) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' not null,
        grv_value_label         varchar(30) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' not null,

        grv_changed             timestamp not null default current_timestamp on update current_timestamp,
        grv_changed_by          bigint unsigned not null,
        grv_created             timestamp not null,
        grv_created_by          bigint unsigned not null,

        PRIMARY KEY (grv_value_id),
        UNIQUE (grv_study_id, grv_value)
    )
    ENGINE=InnoDB
    auto_increment = 200
    CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';
