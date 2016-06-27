import Functions
from Functions import Tree
from rdflib import Graph, util, URIRef
import json

if __name__ == '__main__':
    # ARGUMENTS
    my_model_path = "DVA\\dva_t_rdfxlm.owl"
    delta_path = 'deltas\\delta_processor_deletion.n3'

    my_graph = Graph()
    my_graph.parse(my_model_path, model=util.guess_format(my_model_path))

    delta_graph = Graph()
    delta_graph.parse(file=open(delta_path, "r"), format="n3")

    delta_uri = Functions.findDeltaUriFromDeltaGraph(delta_graph)

    main_dictionary = {}
    main_dictionary['action'] = 'update'
    main_dictionary['subject'] = Functions.findChangeSubjectFromDelta(delta_graph)

    # Find deletions in delta
    # deletion_objects = Functions.findChangeObjectsFromDelta(delta_graph)
    deletion_statements = Functions.findPredicateAndObjectCouplesFromDelta(delta_graph)

    if len(deletion_statements) == 0:
        main_dictionary['action'] = 'insertion'
        main_dictionary['deletions'] = []
    else:
        deletion_dictionary_list = []

        # For each deletion, produce a Tree
        for statement in deletion_statements:
            # Object uri
            obj = statement[1]

            # Predicate uri
            pred = statement[0]

            # Get dictionary of tree
            obj_dictionary = (Tree(my_graph, URIRef(obj))).dictionary

            obj_dictionary['action'] = 'deletion'
            obj_dictionary['predicate'] = pred
            obj_dictionary['object'] = obj

            deletion_dictionary_list.append(obj_dictionary)

        main_dictionary['deletions'] = deletion_dictionary_list

    # Find insertions in delta
    # insertion_objects = Functions.findChangeObjectsFromDelta(delta_graph, 'insertion')
    insertion_statements = Functions.findPredicateAndObjectCouplesFromDelta(delta_graph, 'insertion')

    if len(insertion_statements) == 0:
        main_dictionary['action'] = 'deletion'
        main_dictionary['insertions'] = []
    else:
        # Perform changes in my graph
        my_graph = Functions.applyDeltaChangesToGraph(my_graph, delta_graph)

        insertion_dictionary_list = []

        # For each deletion, produce a Tree
        for statement in insertion_statements:
            # Object uri
            obj = statement[1]

            # Predicate uri
            pred = statement[0]

            # Get dictionary of tree
            obj_dictionary = (Tree(my_graph, URIRef(obj))).dictionary

            obj_dictionary['action'] = 'insertion'
            obj_dictionary['predicate'] = pred
            obj_dictionary['object'] = obj

            insertion_dictionary_list.append(obj_dictionary)

        main_dictionary['insertions'] = insertion_dictionary_list


    json_string = json.dumps(main_dictionary, indent=4)

    # Write to file
    f = open(delta_path[:-3] + '_RESULTS.json', 'w')
    f.write(json_string)
    f.close()
