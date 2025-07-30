###
# CREATE OR REPLACE FUNCTION get_result_cruises(
#   organization_id INTEGER,
#   cruise_id INTEGER,
#   params TEXT
# ) RETURNS TEXT AS $BODY$
# @modules

from ots.search.cruise_search import CruiseSearch

return CruiseSearch(
    plpy=plpy, organization_id=organization_id, cruise_id=cruise_id, params=params
).get_cabins()

###
# $BODY$ LANGUAGE plpythonu VOLATILE
