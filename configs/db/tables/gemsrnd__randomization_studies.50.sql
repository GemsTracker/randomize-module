

CREATE TABLE if not exists gemsrnd__randomization_studies (
        grs_study_id            bigint unsigned not null auto_increment,
        grs_study_name          varchar(30) CHARACTER SET 'utf8' COLLATE 'utf8_general_ci' not null,

        grs_active              boolean not null default 1,
        
        grs_changed             timestamp not null default current_timestamp on update current_timestamp,
        grs_changed_by          bigint unsigned not null,
        grs_created             timestamp not null,
        grs_created_by          bigint unsigned not null,

        PRIMARY KEY (grs_study_id)
    )
    ENGINE=InnoDB
    auto_increment = 40
    CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';
