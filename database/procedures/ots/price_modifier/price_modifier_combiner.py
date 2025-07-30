class SimpleGraphVertex:
    """
    The vertex of a simple graph 
    """

    def __init__(self, idx):
        self.idx = idx
        self.reachable_vertices = set()

    def __len__(self):
        return len(self.reachable_vertices)

    def __str__(self):
        return str(self.idx)


class SimpleGraph:
    """
    A Simple Graph
    """

    def __init__(self, vertices, edges):
        vertices = {v: SimpleGraphVertex(v) for v in vertices}
        edges = [(vertices[a], vertices[b]) for a, b in edges]

        for a, b in edges:
            a.reachable_vertices.add(b)
            b.reachable_vertices.add(a)

        self.vertices = vertices
        self.edges = edges

    def __str__(self):
        # returns the adjacency
        return "\n".join(
            [
                str(v) + ":" + str([str(rv) for rv in v.reachable_vertices])
                for idx, v in self.vertices.iteritems()
            ]
        )


class MaximalDichotomousSubGraphFinder:
    def find_all(self, graph):

        # Definition 1: G(V,E) is a graph, if V the set of vertices, and E the set of edges
        #               G simple graph if there is no multiple edges or loops.
        #               Let v element of V a vertex.
        #               The degree of a vertex is the number of edges connected with the vertex. Symbol: d(v)

        # Definition 2: G dichotomous if every vertices in G connected with each other

        # Theorem 1: the degree of vertices for every dichotomous graph is same
        # Formally: Let G(V,E) a graph,
        #           If G dichotomous, then for every a,b element of V : d(a) = d(b)
        #           where d(x) is the degree of vertex 'x'

        # let v,w,x vertices
        # Theorem 2: Every G({v},{}) is a dichotomous subgraph. (If d(v) = 0 then maximal dichotomous subgraph)
        # Theorem 3: If (v,w) is an edge then G({v,w},{(v,w)}) is a dichotomous subgraph.
        #            (If d(v) = 1 or d(w) = 1 then maximal dichotomous subgraph)
        # Theorem 4: If (v,w), (v,x) are edges and (w,x) edge exists
        #            then G({v,w,x},{(v,w),(v,x),(w,x)}) is a dichotomous subgraph
        # If d(v) = 2 or d(w) = 2 or d(x) = 2, then maximal dichotomous subgraph
        # ...

        maximal_dichotomous_subgraphs = set()
        for idx, v in graph.vertices.iteritems():
            if len(v) == 0:
                # The degree of v is 0.
                # There is no connected edges with this vertex, so this is maximal.
                # Every vertex is connected with every vertex (without loops, because it is a simple graph)
                # so it is dichotomous.
                maximal_dichotomous_subgraphs.add(frozenset({v}))
            else:
                # We know, every vertex in G is part of a maximal dichotomous subgraph.
                # A v vertex can be part of more maximal dichotomous subgraphs, but edges cannot.
                # In order to find a maximal dichotomous subgraph, we need to know a direction (an edge)

                # to get an edge we can use the reachable_vertices
                for w in v.reachable_vertices:
                    # We know, the vertex v is connected with every (w) v -reachable vertex
                    # v.reachable intersection w.reachable gives the common vertices
                    common_vertices = v.reachable_vertices.intersection(w.reachable_vertices)

                    # so v and w is connected with every common vertices
                    # we have to check if the common vertices is connected with each other
                    if len(common_vertices) > 0:
                        if self._is_this_dichotomous_subgraph(common_vertices):
                            maximal_dichotomous_subgraphs.add(frozenset(common_vertices.union({v, w})))
                    else:
                        # there is no common vertices so this is a single line connection
                        # which is a maximal dichotomous subgraph
                        maximal_dichotomous_subgraphs.add(frozenset({v, w}))
        return [[j.idx for j in i] for i in maximal_dichotomous_subgraphs]

    def _is_this_dichotomous_subgraph(self, vertices):
        """
        :param vertices: list<SimpleGraphVertex>
        :return:
        """
        # There are n vertices.
        # Check the 1. vertex so if it can reach the n-1 vertices
        # Check the 2. vertex so if it can reach the n-2 vertices
        # ...
        # Check the n-1 th vertex so if it can reach the last 1 vertex
        vertices = list(vertices)
        n = len(vertices)
        for i in range(n):
            for j in range(i + 1, n):
                if not vertices[j] in vertices[i].reachable_vertices:
                    return False
        return True


class PriceModifierCombiner:
    """
    Creates cases from price modifiers combinations
    """

    def combine(self, price_modifier_ids, combinations):
        # the price_modifier_combinations may contains invalid price_modifier_id
        # so here it is a quick fix for that:
        combinations = [
            (a, b) for a, b in combinations if a in price_modifier_ids and b in price_modifier_ids
        ]

        # Assume that there is a graph with:
        graph = SimpleGraph(vertices=price_modifier_ids, edges=combinations)

        # The solution is to find the all MAXIMAL DICHOTOMOUS SUBGRAPH
        subgraphs = MaximalDichotomousSubGraphFinder().find_all(graph)

        return subgraphs
