from rdflib import Graph, URIRef
import json

# Define prefixes
rdf = "prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> "
rdfs = "prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> "
xml = "prefix xml: <http://www.w3.org/2001/XMLSchema#> "
owl = "prefix owl: <http://www.w3.org/2002/07/owl#> "
# dva = "prefix dva: <https://dl.dropboxusercontent.com/u/27469926/dva_t.owl#> "
lrm = "prefix lrm: <http://xrce.xerox.com/LRM#> "

prefixes = rdf + rdfs + xml + owl + lrm  # + dva


def executeSPARQLSelectToGraph(graph, query):
    return graph.query(prefixes + query)

def getLabelOfInstanceFromGraph(my_graph, instance):
    query = "SELECT DISTINCT ?label WHERE { <" + instance + "> rdfs:label ?label .}"

    qres = executeSPARQLSelectToGraph(my_graph, query)

    if len(qres.result) == 0:
        return False
    else:
        return ([result[0] for result in qres.result])[0]

def getAssociatedDependencyURIsOfResource(resource, graph):

    query = "SELECT ?dependency WHERE " \
            "{ " \
                "{ " \
                "?dependency lrm:from <" + resource.uri + "> ." \
                "} " \
            "UNION " \
                "{ " \
                "?dependency lrm:to <" + resource.uri + "> ." \
                "} " \
            "}"

    qres = executeSPARQLSelectToGraph(graph, query)

    dependencies = []

    for result in qres.result:
        dependencies.append(result[0])

    return dependencies

def getToResourceURIsOfDependency(dependency, graph):

    query = "SELECT ?resource WHERE " \
            "{ " \
                "<" + dependency.uri + "> lrm:to ?resource ." \
            "}"

    qres = executeSPARQLSelectToGraph(graph, query)

    resource_uris = []

    for result in qres.result:
        resource_uris.append(result[0])

    return resource_uris

def getFromResourceURIsOfDependency(dependency, graph):

    query = "SELECT ?resource WHERE " \
            "{ " \
                "<" + dependency.uri + "> lrm:from ?resource ." \
            "}"

    qres = executeSPARQLSelectToGraph(graph, query)

    resource_uris = []

    for result in qres.result:
        resource_uris.append(result[0])

    return resource_uris

def getConjunctiveOrDisjunctiveTypeOfDependency(my_graph, dependency):
    dependency_type = ''

    # query = "SELECT DISTINCT ?dependency_type WHERE" \
    #         "{ " \
    #             "<" + dependency.uri + "> rdf:type ?dependency_type . " \
    #             "FILTER(" \
    #                 "?dependency_type != dva:HardwareDependency && " \
    #                 "?dependency_type != dva:SoftwareDependency && " \
    #                 "?dependency_type != dva:DataDependency && " \
    #                 "?dependency_type != owl:NamedIndividual" \
    #             ") " \
    #         "}"

    query = "SELECT DISTINCT ?dependency_type WHERE" \
            "{ " \
                "<" + dependency.uri + "> rdf:type ?dependency_type . " \
                "FILTER(" \
                    "?dependency_type = <http://xrce.xerox.com/LRM#ConjunctiveDependency> || " \
                    "?dependency_type = <http://xrce.xerox.com/LRM#DisjunctiveDependency>" \
                ") " \
            "}"

    qres = executeSPARQLSelectToGraph(my_graph, query)

    for row in qres.bindings:
        dependency_type = row['dependency_type']

    if dependency_type == URIRef('http://xrce.xerox.com/LRM#DisjunctiveDependency'):
        return 'Disjunctive'
    else:
        return 'Conjunctive'

def getIntetionOfDependency(my_graph, dependency):
    intention_value = ''

    query = "SELECT ?intention_value WHERE" \
            "{ " \
                "<" + dependency.uri + "> lrm:intention ?intention . " \
                "?intention rdfs:label ?intention_value " \
            "}"

    qres = executeSPARQLSelectToGraph(my_graph, query)

    for row in qres.bindings:
        intention_value = row['intention_value']

    return intention_value  # e.g. Functional

def getPredicateBetweenResourceAndDependency(graph, resource, dependency):
    predicate = 'to'

    query = "SELECT ?predicate WHERE" \
            "{ " \
                "<" + dependency.uri + "> ?predicate <" + resource.uri + "> . " \
            "}"

    qres = executeSPARQLSelectToGraph(graph, query)

    for row in qres.bindings:
        if row['predicate'] == URIRef('http://xrce.xerox.com/LRM#from'):
            predicate = 'from'

    return predicate

# def askIfTripleExists(my_graph, subject, predicate, object=None):
#
#     if object == None:
#         query = "ASK " \
#             "{ " \
#             "<" + subject + "> <" + predicate + "> ?object ." \
#             "}"
#     else:
#         query = "ASK " \
#             "{ " \
#             "<" + subject + "> <" + predicate + "> <" + object + "> ." \
#             "}"
#
#     return bool(executeSPARQLSelectToGraph(my_graph, query))
#
#
# def findChangeTypeForDelta(my_graph, delta):
#
#     deletion_qres = askIfTripleExists(my_graph, delta, "http://xrce.xerox.com/LRM#deletion", None)
#
#     insertion_qres = askIfTripleExists(my_graph, delta, "http://xrce.xerox.com/LRM#insertion", None)
#
#     if deletion_qres == True and insertion_qres == True:
#         return "update"
#     elif deletion_qres == True and insertion_qres == False:
#         return "deletion"
#     elif deletion_qres == False and insertion_qres == True:
#         return "insertion"
#     else:
#         return "none"

def findDeltaUriFromDeltaGraph(delta_graph):
    result = executeSPARQLSelectToGraph(delta_graph, 'SELECT ?delta WHERE {?subject <http://xrce.xerox.com/LRM#changedBy> ?delta}')

    for r in result:
        return r[0]

def findChangeSubjectFromDelta(delta_graph):
    result = executeSPARQLSelectToGraph(delta_graph, 'SELECT ?subject WHERE {?subject <http://xrce.xerox.com/LRM#changedBy> ?delta}')

    for r in result:
        return r[0]

def findChangeObjectsFromDelta(delta_graph, change_type = 'deletion'):

    query = 'SELECT ?object WHERE' \
            '{?delta <http://xrce.xerox.com/LRM#' + change_type + '> ?statement .' \
            '?statement <http://www.w3.org/1999/02/22-rdf-syntax-ns#object> ?object' \
            '}'

    results = executeSPARQLSelectToGraph(delta_graph, query)

    objects = []

    for r in results:
        objects.append(r[0])

    return objects

def findPredicateAndObjectCouplesFromDelta(delta_graph, change_type = 'deletion'):
    query = 'SELECT ?predicate ?object WHERE' \
            '{?delta <http://xrce.xerox.com/LRM#' + change_type + '> ?statement .' \
            '?statement <http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate> ?predicate .' \
            '?statement <http://www.w3.org/1999/02/22-rdf-syntax-ns#object> ?object .' \
            '}'

    results = executeSPARQLSelectToGraph(delta_graph, query)

    statements = []

    for r in results:
        statements.append([r[0], r[1]])

    return statements

def applyDeltaChangesToGraph(my_graph, delta_graph):

    # Get delta changed resource
    subject = findChangeSubjectFromDelta(delta_graph)

    # Get delta deletion predicate and object couples (statements)
    deletion_statements = findPredicateAndObjectCouplesFromDelta(delta_graph)

    # Perform deletions
    for statement in deletion_statements:
        my_graph.remove((URIRef(subject), URIRef(statement[0]), URIRef(statement[1])))

    # Get delta insertion predicate and object couples (statements)
    insertion_statements = findPredicateAndObjectCouplesFromDelta(delta_graph, change_type='insertion')

    # Perform insertions
    for statement in insertion_statements:
        my_graph.add((URIRef(subject), URIRef(statement[0]), URIRef(statement[1])))

    return my_graph

class Dependency:
    def __init__(self, uri, graph, tree, parent_resource):
        self.uri = uri
        self.tree = tree
        self.graph = graph
        self.parent_resource = parent_resource

        self.tree.dependency_nodes_uris.append(self.uri)

        self.from_resource_uris = []
        self.to_resource_uris = []
        self.children_resource_nodes = []

        # Label
        self.label = getLabelOfInstanceFromGraph(graph, self.uri)
        if self.label == False:
            self.label = ''

        # Intention
        self.intention = getIntetionOfDependency(graph, self)

        # Conjunctive or disjunctive
        self.type = getConjunctiveOrDisjunctiveTypeOfDependency(graph, self)

        # Find to resources
        self.findToResources(graph)

        # # Find from resources
        self.findFromResources(graph)

    def findToResources(self, graph):
        resource_uris = getToResourceURIsOfDependency(self, graph)

        for uri in resource_uris:
            self.to_resource_uris.append(uri)

            if uri not in self.tree.resource_nodes_uris:

                new_resource = Resource(uri, graph, self.tree, self)

                self.children_resource_nodes.append(new_resource)
                self.tree.resource_nodes.append(new_resource)

    def findFromResources(self, graph):
        resource_uris = getFromResourceURIsOfDependency(self, graph)

        for uri in resource_uris:
            self.from_resource_uris.append(uri)

            if uri not in self.tree.resource_nodes_uris:

                new_resource = Resource(uri, graph, self.tree, self)

                self.children_resource_nodes.append(new_resource)
                self.tree.resource_nodes.append(new_resource)

    def createDictionaryOfDependency(self):
        d = {}
        d['name'] = self.label
        d['type'] = 'Dependency'
        d['dependencyType'] = self.type
        d['intention'] = self.intention

        d['link'] = {'label': getPredicateBetweenResourceAndDependency(self.graph, self.parent_resource, self), 'direction': 'parent'}

        children_list_of_dicts = []

        for child_resource in self.children_resource_nodes:
            children_list_of_dicts.append(child_resource.createDictionaryOfResource())

        if children_list_of_dicts != []:
            d['children'] = children_list_of_dicts

        return d


class Resource:
    def __init__(self, uri, graph, tree, parent_dependency):
        self.uri = uri
        self.tree = tree
        self.parent_dependency = parent_dependency
        self.graph = graph

        self.tree.resource_nodes_uris.append(self.uri)

        # Search for label in graph
        self.label = getLabelOfInstanceFromGraph(graph, self.uri)

        # If label not found, set empty label string
        if self.label == False:
            self.label = ''

        # # Find associated dependencies
        self.associated_dependency_uris = []
        self.children_dependency_nodes = []
        self.findAssociatedDependencies(graph)

    def findAssociatedDependencies(self, graph):

        # Search for associated dependencies
        associated_dependency_uris = getAssociatedDependencyURIsOfResource(self, graph)

        for dependency_uri in associated_dependency_uris:
            self.associated_dependency_uris.append(dependency_uri)

            if dependency_uri not in self.tree.dependency_nodes_uris:

                # Create a dependency
                new_dependency = Dependency(dependency_uri, graph, self.tree, self)

                # Append to lists
                self.children_dependency_nodes.append(new_dependency)
                self.tree.dependency_nodes.append(new_dependency)

    def createDictionaryOfResource(self):
        d = {}
        d['name'] = self.label
        d['type'] = 'Resource'

        if self.parent_dependency != None:
            to_or_from_predicate = getPredicateBetweenResourceAndDependency(self.graph, self, self.parent_dependency)
            d['link'] = {'label': to_or_from_predicate, 'direction': 'self'}


        if self.parent_dependency == None:
            d['impacted'] = True
            # d['change'] = self.tree.change_description
        elif self.parent_dependency.type == 'Conjunctive' and to_or_from_predicate == 'to':
            d['impacted'] = True
        else:
            d['impacted'] = False

        children_list_of_dicts = []

        for child_dependency in self.children_dependency_nodes:
            children_list_of_dicts.append(child_dependency.createDictionaryOfDependency())

        if children_list_of_dicts != []:
            d['children'] = children_list_of_dicts

        return d


class Tree():
    def __init__(self, graph, changed_resource_uri, change_description=None):
        self.graph = graph
        self.change_description = change_description

        self.resource_nodes = []
        self.dependency_nodes = []
        self.resource_nodes_uris = []
        self.dependency_nodes_uris = []

        # Create an instance of the changed resource
        self.changed_resource = Resource(changed_resource_uri, self.graph, self, None)

        self.resource_nodes.append(self.changed_resource)

        self.buildJson()

    def buildJson(self):

        self.dictionary = self.changed_resource.createDictionaryOfResource()

        #self.json = json.dumps(dictionary, indent=4)

        # f = open('result_tree.json', 'w')
        # f.write(j)
        # f.close()