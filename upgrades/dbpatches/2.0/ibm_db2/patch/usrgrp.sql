ALTER TABLE usrgrp ALTER COLUMN usrgrpid SET WITH DEFAULT NULL;
REORG TABLE usrgrp;
ALTER TABLE usrgrp DROP COLUMN api_access;
REORG TABLE usrgrp;
