<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{action('\Modules\Accounting\Http\Controllers\AccountingController@index')}}"><i class="fas fa-balance-scale"></i> &nbsp;Accounting</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling --> 
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li @if(request()->segment(1) == 'accounting' && request()->segment(2) == ''))  class="active" @endif><a href="{{action('\Modules\Accounting\Http\Controllers\AccountingController@index')}}"> <i class="fa fas fa-tachometer-alt"></i> &nbsp;Dashboard</a></li>
                   
                    <li @if( request()->segment(1) == 'accounting' && request()->segment(2) == 'control-accounts') class="dropdown active" @else class="dropdown"  @endif >
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Debtors / Creditors<i class="fas fa-chevron-circle-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\AccountingController@controlACIndex',['debtor'])}}">Debtor Accounts</a></li>
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\AccountingController@controlACIndex',['creditors'])}}">Creditor Accounts</a></li>
                          </ul>
                    </li>   

                    <li @if(request()->segment(1) == 'accountings') class="dropdown active" @else class="dropdown" @endif>
                        <a  class="dropdown-toggle" data-toggle="dropdown" href="#">Transactions<i class="fas fa-chevron-circle-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountTransactionController@index')}}">Transactions</a></li>   
                            <li><a href="#">Payment Adjustment</a></li>
                            <li><a href="#"></a></li>                         

                             

                        </ul>
                    </li>
                    
                    <li @if(request()->segment(1) == 'accountings') class="dropdown active" @else class="dropdown" @endif>
                        <a  class="dropdown-toggle" data-toggle="dropdown" href="#">Payments <i class="fas fa-chevron-circle-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountPaymentController@addReceipt')}}">Receipt - Receivable</a></li>                            
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountPaymentController@addPettyCashVoucher')}}">Petty Cash Voucher - Payable</a></li> 
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountPaymentController@addChequeVoucher')}}">Cheque Payment Voucher - Payable</a></li> 
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\AccountingController@controlACIndex',['creditors'])}}">Debit Note - Receivable</a></li>                            
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\AccountingController@controlACIndex',['creditors'])}}">Credit Note - Payable</a></li>    

                        </ul>
                    </li>

                    <li @if(request()->segment(1) == 'accountings') class="dropdown active" @else class="dropdown" @endif>
                        <a  class="dropdown-toggle" data-toggle="dropdown" href="#">Banking <i class="fas fa-chevron-circle-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Bank Accounts</a></li>
                            <li><a href="#">Bank Debit Advice</a></li>
                            <li><a href="#">Bank Credit Advice</a></li> 
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\BankReconcilationController@index')}}">Bank Reconciliation</a></li>
                            <li><a href="#" disabled>Cheque Return Note</a></li>
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\DoubleEntryBankDepositController@index')}}">Bank Deposit</a></li>
                        </ul>
                    </li>

                    <li @if(request()->segment(1) == 'accountings') class="dropdown active" @else class="dropdown" @endif>
                        <a  class="dropdown-toggle" data-toggle="dropdown" href="#">Reports <i class="fas fa-chevron-circle-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountReportController@trialBalance')}}">Trial Balance</a></li> 
                            <li><a href="{{action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountReportController@pnlReport')}}">Profit & Loss Report</a></li> 
                        </ul>
                    </li>

                    
                    <li @if(request()->segment(1) == 'accountings') class="active" @endif><a href="{{action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountPaymentController@addJournalEntry')}}">Journal Entry</a></li>
                    <li @if(request()->segment(1) == 'accounting' && request()->segment(2) == ''))  class="active" @endif><a href="{{action('\Modules\Accounting\Http\Controllers\AccountingController@ledger')}}">Account Ledger</a></li>
                    <li @if(request()->segment(1) == 'accounting' && request()->segment(2) == 'accounts') ) class="active" @endif><a href="{{action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountController@index')}}">Chart Of Accounts</a></li>
                    <li @if(request()->segment(1) == 'accounting' && request()->segment(2) == 'master-data') ) class="active" @endif><a href="{{action('\Modules\Accounting\Http\Controllers\AccountingController@masterData')}}">Master Data</a></li>
                    
                    {{-- @can('manufacturing.access_recipe')
                        <li @if(request()->segment(1) == 'manufacturing' && in_array(request()->segment(2), ['recipe', 'add-ingredient'])) class="active" @endif><a href="{{action('\Modules\Manufacturing\Http\Controllers\RecipeController@index')}}">@lang('manufacturing::lang.recipe')</a></li>
                    @endcan

                    @can('manufacturing.access_production')
                        <li @if(request()->segment(2) == 'production') class="active" @endif><a href="{{action('\Modules\Manufacturing\Http\Controllers\ProductionController@index')}}">@lang('manufacturing::lang.production')</a></li>

                        <li @if(request()->segment(1) == 'manufacturing' && request()->segment(2) == 'settings') class="active" @endif><a href="{{action('\Modules\Manufacturing\Http\Controllers\SettingsController@index')}}">@lang('messages.settings')</a></li>

                        <li @if(request()->segment(2) == 'report') class="active" @endif><a href="{{action('\Modules\Manufacturing\Http\Controllers\ProductionController@getManufacturingReport')}}">@lang('manufacturing::lang.manufacturing_report')</a></li>
                    @endcan --}}
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>