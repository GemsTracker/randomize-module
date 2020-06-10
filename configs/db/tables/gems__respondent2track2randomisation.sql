
CREATE TABLE if not exists gems__respondent2track2randomisation (
        gr2t2r_id_respondent_track  bigint unsigned not null references gems__respondent2track (gr2t_id_respondent_track),
        gr2t2r_id_app_field         bigint unsigned not null references gems__track_appointments (gtap_id_app_field),

        -- gr2t2r_id_appointment       bigint unsigned null references gems__appointments (gap_id_appointment),
        gr2t2r_value_manual         boolean not null default 0,

        gr2t2r_changed    timestamp       not null default current_timestamp on update current_timestamp,
        gr2t2r_changed_by bigint unsigned not null,
        gr2t2r_created    timestamp       not null,
        gr2t2r_created_by bigint unsigned not null,

        PRIMARY KEY (gr2t2r_id_respondent_track, gr2t2r_id_app_field)
    )
    ENGINE = InnoDB
    CHARACTER SET 'utf8'
    COLLATE 'utf8_general_ci';
