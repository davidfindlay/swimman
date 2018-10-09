import {Component, OnInit} from '@angular/core';
import {JoomlaUser} from "../models/joomla-user";
import {Page} from '../models/page'
import {HttpClient, HttpParams} from '@angular/common/http';
import {PagedData} from '../models/paged-data';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import {ViewChild, ElementRef} from '@angular/core';
// import {DatePipe} from '@angular/common';

import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { faLink, faUnlink, faEdit, faSyncAlt, faTrash } from '@fortawesome/free-solid-svg-icons';
import { NgxSpinnerService } from 'ngx-spinner';
import { JoomlaUserLinkModalComponent } from "../joomla-user-link-modal/joomla-user-link-modal.component";
import {DatatableComponent} from "@swimlane/ngx-datatable";

@Component({
    selector: 'app-joomla-user-list',
    templateUrl: './joomla-user-list.component.html',
    styleUrls: ['./joomla-user-list.component.css']
})
export class JoomlaUserListComponent implements OnInit {

    page = new Page();
    users = new Array<JoomlaUser>();

    faLink = faLink;
    faUnlink = faUnlink;
    faEdit = faEdit;
    faSyncAlt = faSyncAlt;
    faTrash = faTrash;

    filterOption: string = 'registered7days';
    filterSearch: string = '';

    public joomlaUserFilterForm: FormGroup;

    // columns = [
    //     {name: 'Joomla Username', prop: 'username'},
    //     {name: 'Display Name', prop: 'name'},
    //     // {name: 'Club Name', prop: 'clubName'},
    //     //{name: 'MSA Number', prop: 'msaNumber'},
    //     {name: 'Registered', prop: 'registerDate'},
    //     {name: 'Linked Member', prop: 'member_id'}
    // ];

    constructor(private httpClient: HttpClient,
                private modalService: NgbModal,
                private formBuilder: FormBuilder,
                private spinner: NgxSpinnerService) {
        setTimeout(() => { this.spinner.hide() }, 1500);
    }

    ngOnInit() {

        this.joomlaUserFilterForm = this.formBuilder.group({
            'filterOption': 'registered7days',
            'filterSearch': ''
        });

        this.page.pageNumber = 0;
        this.page.size = 5;
        this.page.orderBy = 'registered';
        this.page.orderDir = 'desc';

        this.setPage({offset: 0});
        this.onChanges();
    }

    setPage(pageInfo: { count?: number, pageSize?: number, limit?: number, offset?: number }) {

        this.spinner.show();
        this.page.pageNumber = pageInfo.offset;
        this.page.size = pageInfo.pageSize;

        this.reloadTable();
    }

    setSort(sortInfo: { sorts: { dir: string, prop: string }[], column: {}, prevValue: string, newValue: string }) {

        this.spinner.show();
        this.page.orderDir = sortInfo.sorts[0].dir;
        this.page.orderBy = sortInfo.sorts[0].prop;

        this.reloadTable();
    }

    reloadTable() {

        // NOTE: those params key values depends on your API!
        const params = new HttpParams()
            .set('orderBy', `${this.page.orderBy}`)
            .set('orderDir', `${this.page.orderDir}`)
            .set('pageNumber', `${this.page.pageNumber}`)
            .set('pageSize', `${this.page.size}`)
            .set('filterOption', `${this.filterOption}`)
            .set('filterSearch', `${this.filterSearch}`);

        this.httpClient.get(`https://forum.mastersswimmingqld.org.au/swimman/json/juserlist-ng.php`, {params})
            .subscribe((response: PagedData<JoomlaUser>) => {

                // this.page.size = response.data.length;
                this.users = response.data;
                this.page.totalElements = response.count;
                this.spinner.hide();

                },
                (error) => {
                    console.log(error);
                    this.spinner.hide();
                });
    }

    onChanges(): void {
        this.joomlaUserFilterForm.controls['filterSearch'].valueChanges.subscribe(val => {
            this.filterSearch = val;
            this.reloadTable();
        });
        this.joomlaUserFilterForm.controls['filterOption'].valueChanges.subscribe(val => {
            this.spinner.show();
            this.filterOption = val;
            this.reloadTable();
        });

    }

    link_member(userdata) {

        const modalRef = this.modalService.open(JoomlaUserLinkModalComponent, { size: 'lg' });
        modalRef.componentInstance.userdata = userdata;

    }

    refresh(userdata) {

    }

    delete_member(userdata) {

    }

    getDate(dateString: string) {
        return new Date(dateString);
    }

}