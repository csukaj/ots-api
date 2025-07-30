from ots.repository.exceptions.unable_to_convert_data_error import UnableToConvertDataError


class BaseModel(dict):
    def __init__(self, iterable=None, **others):
        if iterable is None:
            iterable = {}
        iterable.update(others)
        super(BaseModel, self).__init__(iterable)

        for key, val in iterable.iteritems():
            setattr(self, key, val)


class AbstractAttribute(object):
    def __init__(self, field_type=None, converter=None, original_field_name=None, original_field_names=None):
        self.type = field_type
        self.converter = converter
        self.original_field_names = (
            [original_field_name] if original_field_name is not None else original_field_names
        )


class Column(AbstractAttribute):
    pass


class Collection(AbstractAttribute):
    pass


class Map(AbstractAttribute):
    def __init__(
        self,
        key_type=None,
        value_type=None,
        converter=None,
        original_field_name=None,
        original_field_names=None,
    ):
        super(Map, self).__init__(dict, converter, original_field_name, original_field_names)

        self.key_type = key_type
        self.value_type = value_type


# Providing aliases may helps readability
HasOne = Column
HasMany = Collection
HasMap = Map


"""
Mapping policy: 
if data is instance of BaseModel we don't convert, just assign it
else we must convert the data before assigning it.

"""


class ObjectMapper:
    def map(self, data, to_type):
        # type: (any, classobj or type) -> to_type
        # map is just an alias to map_column
        return self.map_column(data, to_type)

    def map_all(self, data_list, to_type):
        # type: (list, classobj or type) -> list
        # map_all is just an alias to map_collection
        return self.map_collection(data_list, to_type)

    def map_column(self, data, to_type, _generic_conversion_is_needed=True):
        # type: (any, classobj or type, bool) -> to_type
        if to_type is None:
            return None
        elif issubclass(to_type, BaseModel) and isinstance(data, BaseModel):
            return data
        elif issubclass(to_type, BaseModel) and isinstance(data, dict):
            return self._get_converted_data_to_model(data, to_type)
        elif data is None:
            return None
        elif _generic_conversion_is_needed:
            return self._get_converted_data_to_generic(data, to_type)
        return data

    def map_collection(self, data_list, to_type, _generic_conversion_is_needed=True):
        # type: (list, classobj or type, bool) -> list
        return [self.map_column(data, to_type, _generic_conversion_is_needed) for data in data_list]

    def map_map(self, data_map, key_type, value_type, _generic_conversion_is_needed=True):
        # type: (dict, classobj or type, classobj or type, bool) -> dict
        new_map = {}

        for key, val in data_map.iteritems():
            new_key = self.map_column(key, key_type)
            new_val = self.map_column(val, value_type, _generic_conversion_is_needed)
            new_map[new_key] = new_val

        return new_map

    def _get_converted_data_to_model(self, data, base_model_type):
        attribute_names = [
            attribute
            for attribute in vars(base_model_type)
            if isinstance(vars(base_model_type)[attribute], AbstractAttribute)
        ]

        new_model = base_model_type({})

        for attribute_name in attribute_names:
            attribute = vars(base_model_type)[attribute_name]
            attribute_type = attribute.type if attribute.type is not None else base_model_type

            converter_input = self._get_converter_input(base_model_type, attribute_name, data)
            covnerted_data = self._get_custom_converted_data(
                vars(base_model_type)[attribute_name].converter, converter_input
            )

            if isinstance(attribute, Column):
                new_model[attribute_name] = self.map_column(
                    covnerted_data, attribute_type, _generic_conversion_is_needed=False
                )
            elif isinstance(attribute, Collection):
                data_to_map = covnerted_data if covnerted_data is not None else []
                new_model[attribute_name] = self.map_collection(
                    data_to_map, attribute_type, _generic_conversion_is_needed=False
                )
            elif isinstance(attribute, Map):
                map_value_type = attribute.value_type if attribute.value_type is not None else base_model_type
                map_key_type = attribute.key_type
                data_to_map = covnerted_data if covnerted_data is not None else {}
                new_model[attribute_name] = self.map_map(
                    data_to_map, map_key_type, map_value_type, _generic_conversion_is_needed=False
                )
            else:
                new_model[attribute_name] = None

            setattr(new_model, attribute_name, new_model[attribute_name])

        return new_model

    def _get_custom_converted_data(self, converter, converter_input):
        if converter_input is not None and converter is not None:
            return converter(*converter_input)
        elif converter_input is not None:
            return converter_input[0]
        return None

    def _get_converter_input(self, base_model_type, attr, data):
        converter_input = []
        if vars(base_model_type)[attr].original_field_names is not None:
            converter_input = [
                data.get(field_name)
                for field_name in vars(base_model_type)[attr].original_field_names
                if data.get(field_name) is not None
            ]

        # if original_field_names are not found then try to find the data under the class member's name key
        if not len(converter_input) and data.get(attr) is not None:
            converter_input = [data.get(attr)]
        elif not len(converter_input):
            converter_input = None

        return converter_input

    def _get_converted_data_to_generic(self, data, to_type):
        try:
            return to_type(data)
        except:
            raise UnableToConvertDataError(
                "Cannot convert {} to {}, please provide a custom converter.".format(data, to_type)
            )
