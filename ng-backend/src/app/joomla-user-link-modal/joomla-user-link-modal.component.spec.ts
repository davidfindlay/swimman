import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { JoomlaUserLinkModalComponent } from './joomla-user-link-modal.component';

describe('JoomlaUserLinkModalComponent', () => {
  let component: JoomlaUserLinkModalComponent;
  let fixture: ComponentFixture<JoomlaUserLinkModalComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ JoomlaUserLinkModalComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(JoomlaUserLinkModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
