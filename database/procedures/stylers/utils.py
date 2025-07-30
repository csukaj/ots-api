import hashlib

import jsonpickle

global_query_cache = {}
query_cache = {}


def execute_cached_query(plpy, query, is_global=False):
    global query_cache
    global global_query_cache
    m = hashlib.md5()
    m.update(query)
    key = m.hexdigest()
    cache = query_cache if not is_global else global_query_cache
    if key not in cache:
        result = plpy.execute(query)
        cache[key] = result
    return cache[key]


def clear_query_cache():
    global query_cache
    query_cache = {}


def sort_dict_to_tuples(dictionary):
    return [(k, dictionary[k]) for k in sorted(dictionary.keys())]


"""
Return sql resultset as array of plain dictionary (which is json_serializable)
"""


def pickle_resultset(results):
    resultset = []
    for row in results:
        pickled = jsonpickle.decode(jsonpickle.encode(row, unpicklable=False))
        try:
            resultset.append(pickled[0])
        except:
            resultset.append(pickled)
    return resultset


def underscore_to_camelcase(name):
    name_arr = name.split("_")
    class_name = ""
    for chars in name_arr:
        class_name += chars.title()
    return class_name
