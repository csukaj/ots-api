from age_resolver import AgeResolver
from copy import deepcopy


class UsageRequestHandler:
    def __init__(self, plpy, age_rangeable_type, age_rangeable_id):
        self.plpy = plpy
        self.request = None
        self.age_resolver = (
            AgeResolver(self.plpy, age_rangeable_type, age_rangeable_id)
            if age_rangeable_type is not None and age_rangeable_id is not None
            else None
        )
        self.named_age_ranges = (
            self.age_resolver.get_age_ranges_dict() if self.age_resolver is not None else None
        )

    def set_request(self, request):
        self.request = deepcopy(request)
        for request_element in self.request:
            request_element["usage"].sort(key=lambda request_item: request_item["age"], reverse=True)

    def get_pax(self):
        pax = 0
        for request_element in self.request:
            for usage in request_element["usage"]:
                pax += usage["amount"]
        return pax
