import unittest
from ots.repository.model.price import Price
from ots.repository.exceptions.unsupported_operation_error import UnsupportedOperationError


class TestPrice(unittest.TestCase):
    def test_creation_of_price(self):

        self.assertRaises(TypeError, lambda: Price(net=2))
        self.assertRaises(TypeError, lambda: Price(rack=3))
        self.assertRaises(TypeError, lambda: Price(margin=1))

        p1 = Price(net=2, rack=3)
        p2 = Price(net=2, margin=1)
        p3 = Price(rack=3, margin=1)

        self.assertAlmostEqual(p1.net, 2.0, 5)
        self.assertAlmostEqual(p2.net, 2.0, 5)
        self.assertAlmostEqual(p3.net, 2.0, 5)

        self.assertAlmostEqual(p1.rack, 3.0, 5)
        self.assertAlmostEqual(p2.rack, 3.0, 5)
        self.assertAlmostEqual(p3.rack, 3.0, 5)

        self.assertAlmostEqual(p1.margin, 1, 5)
        self.assertAlmostEqual(p2.margin, 1, 5)
        self.assertAlmostEqual(p3.margin, 1, 5)

    def test_price_operations(self):
        p1 = Price(net=2, rack=3)
        p2 = Price(net=2, rack=3)

        self.assertTrue(p1 == p2)
        self.assertTrue(p1 + p2 == Price(net=4, rack=6))
        self.assertTrue(p1 * 2 == Price(net=4, rack=6))
        self.assertTrue(2 * p1 == Price(net=4, rack=6))
        self.assertTrue(-p1 == Price(net=-2, rack=-3))
        self.assertTrue(p1 / 2 == Price(net=1, rack=1.5))

        self.assertRaises(UnsupportedOperationError, lambda: p1 == 2)
        self.assertRaises(UnsupportedOperationError, lambda: p1 + 2)
        self.assertRaises(UnsupportedOperationError, lambda: p1 * p2)
        self.assertRaises(UnsupportedOperationError, lambda: p1 / p2)

    def test_margin_calculation(self):
        p1 = Price(net=2, rack=3)

        self.assertAlmostEqual(p1.margin, 1, 5)
