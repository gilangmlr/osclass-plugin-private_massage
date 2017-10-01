CREATE TABLE oc_t_message_room (
    pk_i_message_room_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fk_i_item_id INT UNSIGNED NOT NULL,
    fk_i_buyer_id INT UNSIGNED NOT NULL,

        PRIMARY KEY (pk_i_message_room_id),
        FOREIGN KEY (fk_i_item_id) REFERENCES oc_t_item (pk_i_id),
        FOREIGN KEY (fk_i_buyer_id) REFERENCES oc_t_user (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

CREATE TABLE oc_t_message (
    pk_i_message_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fk_i_message_room_id INT UNSIGNED NOT NULL,
    fk_i_sender_id INT UNSIGNED NOT NULL,
    s_content TEXT NOT NULL,
    dt_delivery_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        PRIMARY KEY (pk_i_message_id),
        FOREIGN KEY (fk_i_message_room_id) REFERENCES oc_t_message_room (pk_i_message_room_id),
        FOREIGN KEY (fk_i_sender_id) REFERENCES oc_t_user (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';