from ots.repository.exceptions.unsupported_operation_error import UnsupportedOperationError


class Price(dict):
    def __init__(self, net=None, rack=None, margin=None):
        # type: (float or int or None, float or int or None, float or int or None) -> None

        net = float(net) if net is not None else None
        rack = float(rack) if rack is not None else None
        margin = float(margin) if margin is not None else None
        if (
            (net is not None and rack is not None)
            or (net is not None and margin is not None)
            or (rack is not None and margin is not None)
        ):

            self.net = net if net is not None else rack - margin
            self.rack = rack if rack is not None else net + margin
        else:
            raise TypeError("You must provide at least two of net,rack,margin.")

        super(Price, self).__init__({"net": self.net, "rack": self.rack, "margin": self.margin})

    @property
    def margin(self):
        if self.net == 0:
            return 0

        return self.rack - self.net

    def __add__(self, other):
        if isinstance(other, Price):
            return Price(net=self.net + other.net, rack=self.rack + other.rack)
        raise UnsupportedOperationError("Only Price + Price is allowed for Price object.")

    def __mul__(self, other):
        if isinstance(other, (float, int)):
            return Price(net=self.net * other, rack=self.rack * other)
        raise UnsupportedOperationError("You can only multiple Price by a constant.")

    def __rmul__(self, other):
        return self.__mul__(other)
    
    def __neg__(self):
        return self.__mul__(-1)

    def __div__(self, other):
        if isinstance(other, (float, int)):
            return Price(net=self.net / other, rack=self.rack / other)
        raise UnsupportedOperationError("You can only divide Price by a constant.")

    def __eq__(self, other):
        if isinstance(other, Price):
            allowed_error = 0.0000001
            return abs(self.net - other.net) <= allowed_error and abs(self.rack - other.rack) <= allowed_error
        raise UnsupportedOperationError("You can only compare Price by Price")
