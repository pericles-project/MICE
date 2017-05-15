@extends('layouts.master')

@section('content')
<!-- End Navigation -->
<div class="container-fluid main-content">
  @if (empty($action) == false)
  <div class="row widget-container fluid-height">
    <div class="col-md-12">
      <div class="widget-content padded">
          @if (isset($action) == true)
              @if ($action == 'accept')
                <h4 class="text-center"><span class="text-success">Change accepted</span></h4>
                <p class="text-center">The change has been accepted and saved.</p>
              @elseif ($action == 'reject')
                <h4 class="text-center"><span class="text-danger">Change rejected</span></h4>
                <p class="text-center">The change has been rejected.</p>
            @endif
          @endif
        </div>
      </div>
    </div>
  @endif

  <div class="row widget-container fluid-height">
    <div class="col-md-12">
      <div class="widget-content padded">
          @if ($results['totalImpact'] == 0)
            <h4 class="text-center"><span class="text-success"><i class="fa fa-check"></i> No impact found</span></h4>
          @else
            <h4 class="text-center"><span class="text-danger"><i class="fa fa-exclamation-circle"></i> Impact found</span></h4>
            <p class="text-center">{{ $results['totalImpact'] }} @if(count($results['totalImpact'] == 1)) resources @else resource @endif might be impacted by this change.</p>
          @endif
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12 no-padding">
      <div class="widget-container scrollable">
        <div class="heading">
          <i class="fa fa-pencil-square-o"></i> Change description
          @if (isset($action) == false)
          <a href="#modal-reject-change" class="btn btn-danger pull-right" data-toggle="modal" data-target="#modal-reject-change"><i class="fa fa-trash-o"></i> Reject change</a>
          <a href="#modal-accept-change" class="btn btn-success pull-right" data-toggle="modal" data-target="#modal-accept-change"><i class="fa fa-check"></i> Accept change</a>
          @endif
        </div>
        <div class="widget-content padded">
          <div class="table-responsive">
            <table class="table table-hover">
              <tr>
                <th>Action</th>
                <th>Subject</th>
                <th>Predicate</th>
                <th>Object</th>
                <th>Impact</th>
                <th>Impacted Resources</th>
                <th></th>
              </tr>
              @foreach ($results['statements'] as $k => $row)
                <tr @if ($row == end($results['statements'])) class="selected" @endif>
                  <td>
                    @if ($row['action'] == 'insertion')
                      <span class="text-success"><i class="fa fa-plus-square"></i> {{ $row['action'] }}</span>
                    @else
                      <span class="text-danger"><i class="fa fa-minus-square"></i> {{ $row['action'] }}</span>
                    @endif
                  </td>
                  <td>{{ $row['subject'] }}</td>
                  <td>{{ $row['predicate'] }}</td>
                  <td>{{ $row['object'] }}</td>
                  <td>
                    @if ($row['statistics']['impacted'] == 0)
                      <span class="label label-success">No impact</span>
                    @else
                      <span class="label label-danger">Impact found</span>
                    @endif
                  </td>
                  <td>{{ $row['statistics']['impacted'] }}</td>
                  <td><a href="/graph/{{ $k }}" class="btn btn-primary updateGraph ladda-button" data-style="slide-right"><span class="ladda-label">View graph</span></a></td>
                </tr>
              @endforeach
            </table>
          </div>
          <!-- <p class="bg-danger padded">2 impacted resources found.</p> -->
          <!-- <div class="btn-toolbar">
            <a href="#" class="btn btn-success">Accept change</a>
            <a href="#" class="btn btn-danger">Reject change</a>
          </div> -->
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics -->
  <!-- <div class="widget-container stats-container row">
    <div class="col-md-4">
      <div class="number" id="totalResourcesCount">
        10
      </div>
      <div class="text">
        Total resources
      </div>
    </div>
    <div class="col-md-4">
      <div class="number text text-danger" id="impactedResourcesCount">
        2
      </div>
      <div class="text">
        Impacted
      </div>
    </div>
    <div class="col-md-4">
      <div class="number text text-success" id="notImpactedResourcesCount">
        8
      </div>
      <div class="text">
        Not impacted
      </div>
    </div>
  </div> -->

  <div class="row widget-container fluid-height" id="graph-container">
    <div class="col-md-8">
          <div class="heading">
            <i class="fa fa-bar-chart-o"></i>Dependency graph
            <span class="pull-right">
                <a class="btn btn-primary" href="#" id="expandAllBtn"><i class="fa fa-plus-square"></i> Expand all</a>
                <a class="btn btn-primary"href="#" id="collapseAllBtn"><i class="fa fa-minus-square"></i>Collapse all</a>
            </span>
          </div>
          <div id="graph-loading" class="text-center">
              <i class="fa fa-spinner fa fa-spin"></i> Loading...
          </div>
          <div id="graph">
            <!-- Show graph -->
          </div>
    </div>
     <div class="col-md-4">
         <div id="graphNotes">
             <div class="heading">
               <i class="fa fa-info-circle"></i>Legend
             </div>
             <div class="widget-content padded">
               <div class="table-responsive">
                 <table>
                   <tr>
                     <td colspan="2"><strong>Node types:</strong></td>
                   </tr>
                   <tr>
                     <td>
                       <svg height="30" width="30">
                         <circle cx="15" cy="15" r="10" fill="#ccc" />
                       </svg>
                     </td>
                     <td>Digital Resource</td>
                   </tr>
                   <tr>
                     <td>
                       <svg height="30" width="30">
                         <circle cx="15" cy="15" r="10" fill="#ccc" stroke="#666" stroke-width="2px"/>
                       </svg>
                     </td>
                     <td>Digital Resource - terminal node</td>
                   </tr>
                   <tr>
                     <td>
                       <svg width="30" height="30">
                           <rect x="14" y="-6" width="17" height="17" transform="rotate(45)" fill="#ccc"/>
                       </svg>
                     </td>
                     <td>Dependency</td>
                   </tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr>
                     <td colspan="2"><strong>Dependency types:</strong></td>
                   </tr>
                   <tr>
                     <td>
                       <svg height="30" width="30">
                           <rect x="14" y="-6" width="17" height="17" transform="rotate(45)" fill="#9e9ac8"/>
                       </svg>
                     </td>
                     <td>Conjunctive dependency (ALL 'from' should be consistent)</td>
                   </tr>
                   <tr>
                     <td>
                       <svg width="30" height="30">
                           <rect x="14" y="-6" width="17" height="17" transform="rotate(45)" fill="#6baed6"/>
                       </svg>
                     </td>
                     <td>Disjunctive dependency (ANY 'from' should be consistent)</td>
                   </tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr>
                     <td colspan="2"><strong>Status:</strong></td>
                   </tr>
                   <tr>
                     <td>
                       <svg height="30" width="30">
                         <circle cx="15" cy="15" r="10" fill="#e6550d" />
                       </svg>
                     </td>
                     <td>Impacted resource</td>
                   </tr>
                   <!-- <tr>
                     <td>
                       <svg height="30" width="30">
                         <circle cx="15" cy="15" r="10" fill="#fd8d3c" />
                       </svg>
                     </td>
                     <td>Possibly impacted resource</td>
                   </tr> -->
                   <tr>
                     <td>
                       <svg height="30" width="30">
                         <circle cx="15" cy="15" r="10" fill="#74c476" />
                       </svg>
                     </td>
                     <td>Not impacted resource</td>
                   </tr>
                   <tr><td>&nbsp;</td></tr>
                   <tr>
                     <td colspan="2"><strong>Lines:</strong></td>
                   </tr>
                   <tr>
                     <td>
                       <svg height="30" width="30">
                         <line x1="0" y1="15" x2="20" y2="15" style="stroke:#000;stroke-width:2" />
                       </svg>
                     </td>
                     <td>Links to dependent resources</td>
                   </tr>
                   <tr>
                     <td>
                         <svg height="30" width="30">
                           <line x1="0" y1="15" x2="20" y2="15" style="stroke:#ccc;stroke-width:2" />
                         </svg>
                     </td>
                     <td>Links to non-dependent resources</td>
                   </tr>
                 </table>
               </div>
             </div>
         </div>
     </div>


      </div>
    <!-- </div> -->
  </div>
<!-- </div> -->

{{modal('modal-reject-change', 'Reject change?', 'Are you sure you want to reject the change?', ['action' => 'reject'])}}
{{modal('modal-accept-change', 'Accept change?', 'Are you sure you want to accept and save the change?', ['action' => 'accept'])}}
@endsection
