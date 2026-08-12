-- OPTIONAL ONLY.
-- Run this only if latitude=0 and longitude=0 in your existing data are
-- placeholder values created before GPS finished loading.
--
-- It converts those fake coordinates to NULL so the delivery/admin UI does
-- not treat 0,0 as a real customer location.

UPDATE addresses
SET latitude = NULL,
    longitude = NULL
WHERE latitude = 0
  AND longitude = 0;
