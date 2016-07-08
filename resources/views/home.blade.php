@extends('layouts.master')

@section('content')
<!-- End Navigation -->
<div class="container-fluid main-content">
  <div class="row widget-container fluid-height">
    <div class="col-md-12">
      <div class="widget-content padded">
        <h4 class="text-success text-center">
          No impact found.
        </h4>
      </div>
    </div>
  </div>

  <div class="row widget-container fluid-height">
    <div class="col-md-12">
      <div class="">
        <div class="heading">
          <i class="fa fa-pencil-square-o"></i> Change description
          <a href="{{ $params['callback_url'] }}&amp;accept=0" class="btn btn-danger pull-right"><i class="fa fa-trash-o"></i> Reject change</a>
          <a href="{{ $params['callback_url'] }}&amp;accept=1" class="btn btn-success pull-right"><i class="fa fa-check"></i> Accept change</a>
        </div>
        <div class="widget-content padded">
          <div class="table-responsive">
            <table class="table">
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
              <tr>
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
                <td><span class="label label-success">No Impact</span></td>
                <td>0</td>
                <td><a href="/graph/{{ $k }}" class="btn btn-primary updateGraph">View graph</a></td>
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

  <div class="row widget-container fluid-height">
    <div class="col-md-8">
          <div class="heading">
            <i class="fa fa-bar-chart-o"></i>Dependency graph
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
@endsection
