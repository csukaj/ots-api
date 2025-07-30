###
# CREATE OR REPLACE FUNCTION get_result_charters(
#   organization_id INTEGER,
#   organization_group_id INTEGER,
#   params TEXT
# ) RETURNS TEXT AS $BODY$
# @modules

from ots.search.charter_search import CharterSearch

return CharterSearch(
    plpy=plpy, organization_id=organization_id, organization_group_id=organization_group_id, params=params
).get_charters()

###
# $BODY$ LANGUAGE plpythonu VOLATILE
