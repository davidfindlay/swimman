import {BrowserModule} from '@angular/platform-browser';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {NgModule} from '@angular/core';
import {NgbModule} from '@ng-bootstrap/ng-bootstrap';
import {NgxDatatableModule} from '@swimlane/ngx-datatable';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';

import {AppComponent} from './app.component';
import {JoomlaUserListComponent} from './joomla-user-list/joomla-user-list.component';
import {HttpClientModule} from '@angular/common/http';
import {JoomlaUserLinkModalComponent} from "./joomla-user-link-modal/joomla-user-link-modal.component";
import {ReactiveFormsModule} from "@angular/forms";
import {NgxSpinnerModule} from "ngx-spinner";

@NgModule({
    declarations: [
        AppComponent,
        JoomlaUserListComponent,
        JoomlaUserLinkModalComponent
    ],
    imports: [
        BrowserModule,
        BrowserAnimationsModule,
        HttpClientModule,
        NgxDatatableModule,
        NgbModule,
        FontAwesomeModule,
        ReactiveFormsModule,
        NgxSpinnerModule
    ],
    entryComponents: [
        JoomlaUserLinkModalComponent
    ],
    providers: [HttpClientModule],
    bootstrap: [AppComponent]
})
export class AppModule {
}
