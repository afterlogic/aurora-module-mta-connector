CREATE FUNCTION `DP1`(password VARCHAR(255)) RETURNS varchar(128) CHARSET utf8
    READS SQL DATA
    DETERMINISTIC
BEGIN
	DECLARE result VARCHAR(128) DEFAULT '';
	DECLARE passwordLen INT;
	DECLARE decodeByte CHAR(3);
	DECLARE plainBytes VARCHAR(128);
	DECLARE startIndex INT DEFAULT 3;
	DECLARE currentByte INT DEFAULT 1;
	DECLARE hexByte CHAR(3);

	SET passwordLen = LENGTH(password);
	IF passwordLen > 0 AND passwordLen % 2 = 0 THEN
		SET decodeByte = CONV((SUBSTRING(password, 1, 2)), 16, 10);
		SET plainBytes = UNHEX(SUBSTRING(password, 1, 2));

		REPEAT
			SET hexByte = CONV((SUBSTRING(password, startIndex, 2)), 16, 10);
			SET plainBytes = CONCAT(plainBytes, UNHEX(HEX(hexByte ^ decodeByte)));

			SET startIndex = startIndex + 2;
			SET currentByte = currentByte + 1;

		UNTIL startIndex > passwordLen
		END REPEAT;

		SET result = plainBytes;
	END IF;

	RETURN result;
END