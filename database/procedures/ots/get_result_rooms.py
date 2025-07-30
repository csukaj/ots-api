###
# CREATE OR REPLACE FUNCTION get_result_rooms(
#   organization_id INTEGER,
#   params TEXT
# ) RETURNS TEXT AS $BODY$
# @modules

from sys import path

path.append("/var/www/html/ots004/database/procedures/")

from ots.search.room_search import RoomSearch

return RoomSearch(plpy=plpy, organization_id=organization_id, params=params).get_rooms()

###
# $BODY$ LANGUAGE plpythonu VOLATILE
