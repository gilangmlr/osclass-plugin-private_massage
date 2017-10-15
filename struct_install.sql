CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/t_message_room (
    pk_i_message_room_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fk_i_item_id INT UNSIGNED NOT NULL,
    fk_i_buyer_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (pk_i_message_room_id),
    FOREIGN KEY (fk_i_item_id) REFERENCES /*TABLE_PREFIX*/t_item (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/t_message (
    pk_i_message_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fk_i_message_room_id INT UNSIGNED NOT NULL,
    fk_i_sender_id INT UNSIGNED NOT NULL,
    s_content TEXT NOT NULL,
    s_image CHAR(36),
    dt_delivery_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (pk_i_message_id),
    FOREIGN KEY (fk_i_message_room_id) REFERENCES
        /*TABLE_PREFIX*/t_message_room (pk_i_message_room_id) ON DELETE CASCADE,
    FOREIGN KEY (fk_i_sender_id) REFERENCES /*TABLE_PREFIX*/t_user (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/t_message_offer (
    pfk_i_message_offer_id INT UNSIGNED NOT NULL,
    i_offered_price BIGINT(20) NOT NULL,
    fk_c_code CHAR(3) NOT NULL,

    PRIMARY KEY (pfk_i_message_offer_id),
    FOREIGN KEY (pfk_i_message_offer_id) REFERENCES /*TABLE_PREFIX*/t_message (pk_i_message_id),
    FOREIGN KEY (fk_c_code) REFERENCES /*TABLE_PREFIX*/t_currency (pk_c_code)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/t_message_room_status (
    pfk_i_message_room_id INT UNSIGNED NOT NULL,
    fk_i_last_message_id INT UNSIGNED,
    i_unread INT UNSIGNED,
    e_offer_status ENUM('none', 'made', 'accepted', 'declined') NOT NULL DEFAULT 'none',
    fk_i_message_offer_id INT UNSIGNED,

    PRIMARY KEY (pfk_i_message_room_id),
    FOREIGN KEY (pfk_i_message_room_id) REFERENCES /*TABLE_PREFIX*/t_message_room (pk_i_message_room_id),
    FOREIGN KEY (fk_i_last_message_id) REFERENCES /*TABLE_PREFIX*/t_message (pk_i_message_id),
    FOREIGN KEY (fk_i_message_offer_id) REFERENCES /*TABLE_PREFIX*/t_message_offer (pfk_i_message_offer_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

DELIMITER |

CREATE TRIGGER IF NOT EXISTS insert_message_room_id AFTER INSERT ON /*TABLE_PREFIX*/t_message_room
  FOR EACH ROW
  BEGIN
    INSERT INTO /*TABLE_PREFIX*/t_message_room_status (pfk_i_message_room_id) VALUES (NEW.pk_i_message_room_id);
  END;
|

CREATE TRIGGER IF NOT EXISTS update_last_message_id AFTER INSERT ON /*TABLE_PREFIX*/t_message
  FOR EACH ROW
  BEGIN
    UPDATE /*TABLE_PREFIX*/t_message_room_status SET fk_i_last_message_id = NEW.pk_i_message_id
        WHERE pfk_i_message_room_id = NEW.fk_i_message_room_id;
  END;
|

CREATE TRIGGER IF NOT EXISTS update_message_offer_id AFTER INSERT ON /*TABLE_PREFIX*/t_message_offer
  FOR EACH ROW
  BEGIN
    UPDATE /*TABLE_PREFIX*/t_message_room_status SET e_offer_status = 'made', fk_i_message_offer_id = NEW.pfk_i_message_offer_id
        WHERE pfk_i_message_room_id IN
            (SELECT fk_i_message_room_id FROM /*TABLE_PREFIX*/t_message WHERE pk_i_message_id = NEW.pfk_i_message_offer_id);
  END;
|

DELIMITER ;

CREATE TABLE IF NOT EXISTS /*TABLE_PREFIX*/t_message_item_status (
    pfk_i_item_id INT UNSIGNED NOT NULL,
    e_item_status ENUM('for-sale', 'reserved', 'sold') NOT NULL DEFAULT 'for-sale',

    PRIMARY KEY (pfk_i_item_id),
    FOREIGN KEY (pfk_i_item_id) REFERENCES /*TABLE_PREFIX*/t_item (pk_i_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

INSERT INTO /*TABLE_PREFIX*/t_message_item_status (pfk_i_item_id)
    SELECT pk_i_id FROM /*TABLE_PREFIX*/t_item WHERE true;