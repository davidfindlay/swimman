import {Component, Input, OnInit} from '@angular/core';
import { JoomlaUser } from "../models/joomla-user";
import { NgbActiveModal, NgbModal } from "@ng-bootstrap/ng-bootstrap";

@Component({
  selector: 'app-joomla-user-link-modal',
  templateUrl: './joomla-user-link-modal.component.html',
  styleUrls: ['./joomla-user-link-modal.component.css']
})

export class JoomlaUserLinkModalComponent implements OnInit {
    @Input() userdata: JoomlaUser;

    constructor(public activeModal: NgbActiveModal) {
    }

    ngOnInit() {
        console.log(this.userdata);
    }

    getDate(dateString: string) {
        return new Date(dateString);
    }

}
