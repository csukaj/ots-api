import unittest
from ots.repository.utils.object_mapper import ObjectMapper, BaseModel, Column, HasOne, HasMany, HasMap


class ExampleModel(BaseModel):
    example_col = Column(int)
    example_null = Column(int)
    example_converted_col = Column(float)
    example_manually_converted_col = Column(float, lambda x: float(x) + 100)

    example_has_one = HasOne()  # if model is not specified the parent model "ExampleModel" will be used
    example_has_many = HasMany()  # if model is not specified the parent model "ExampleModel" will be used
    example_has_map = HasMap(int, str)

    something_else = Column(int, original_field_name="example_field_name")
    something_else2 = Column(
        int,
        original_field_names=["example_field_name", "example_field_name2"],
        converter=lambda field_a, field_b: field_a + field_b,
    )


class TestObjectMapper(unittest.TestCase):
    def setUp(self):
        self.om = ObjectMapper()
        self.exampleModel = self.om.map(
            {
                "example_col": 1,
                "example_converted_col": 2,
                "example_manually_converted_col": 3,
                "example_has_one": {
                    "example_col": 4,
                    "example_converted_col": 5,
                    "example_manually_converted_col": 6,
                },
                "example_has_many": [
                    {"example_col": 7, "example_converted_col": 8, "example_manually_converted_col": 9},
                    {"example_col": 10, "example_converted_col": 11, "example_manually_converted_col": 12},
                ],
                "example_field_name": 13,
                "example_field_name2": 14,
                "example_has_map": {"2": "something", "4": "something_else"},
            },
            ExampleModel,
        )

    def test_column_that_do_not_need_conversion(self):
        self.assertEqual(self.exampleModel["example_col"], 1)

    def test_attribute_that_is_accessible_without_get_item_protocol(self):
        self.assertEqual(self.exampleModel.example_col, 1)

    def test_column_that_needs_conversion(self):
        self.assertEqual(self.exampleModel["example_converted_col"], 2.0)

    def test_column_that_needs_manual_conversion(self):
        self.assertEqual(self.exampleModel["example_manually_converted_col"], 103.0)

    def test_has_one_connection(self):
        self.assertEqual(self.exampleModel["example_has_one"]["example_col"], 4)
        self.assertEqual(self.exampleModel["example_has_one"]["example_converted_col"], 5.0)
        self.assertEqual(self.exampleModel["example_has_one"]["example_manually_converted_col"], 106.0)

    def test_has_many_connection(self):
        self.assertEqual(self.exampleModel["example_has_many"][0]["example_col"], 7)
        self.assertEqual(self.exampleModel["example_has_many"][0]["example_converted_col"], 8.0)
        self.assertEqual(self.exampleModel["example_has_many"][0]["example_manually_converted_col"], 109.0)

        self.assertEqual(self.exampleModel["example_has_many"][1]["example_col"], 10)
        self.assertEqual(self.exampleModel["example_has_many"][1]["example_converted_col"], 11.0)
        self.assertEqual(self.exampleModel["example_has_many"][1]["example_manually_converted_col"], 112.0)

    def test_original_field_name_parameter(self):
        self.assertEqual(self.exampleModel["something_else"], 13)

    def test_original_field_names_parameter(self):
        self.assertEqual(self.exampleModel["something_else2"], 13 + 14)

    def test_has_map_connection(self):
        self.assertEqual(self.exampleModel["example_has_map"][2], "something")
        self.assertEqual(self.exampleModel["example_has_map"][4], "something_else")
